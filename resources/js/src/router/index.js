import { createRouter, createWebHistory } from 'vue-router';
import routes from './routes';
import useAuth from '../composables/useAuth';
import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const breadcrumbStore = useBreadcrumbStore();

    if (to?.name) {
        const finalCrumbs = [];
        const allRoutes = router.getRoutes();
        let current = allRoutes.find(r => r?.name === to.name);
        let paramsForParent = { ...to.params };

        const buildChain = [];

        // 🔹 recorre hacia arriba (hijo → padre)
        while (current) {
            buildChain.unshift(current);
            const parentName = current.meta?.parent;
            if (!parentName) break;
            current = allRoutes.find(r => r.name === parentName);
        }

        // 🔹 recorre ahora padre → hijo (orden correcto)
        for (const routeRecord of buildChain) {
            const breadcrumbMeta = routeRecord.meta?.breadcrumb;

            if (!breadcrumbMeta) continue;

            if (typeof breadcrumbMeta === 'function') {
                const result = await breadcrumbMeta({ params: paramsForParent });
                const crumb = result.crumb || result;

                const idKey = Object.keys(paramsForParent).find(k => k.toLowerCase().includes('id'));
                const idValue = idKey ? paramsForParent[idKey] : null;

                const existing = idValue ? await breadcrumbStore.findTextById(idValue) : null;

                const newCrumb = {
                    text: crumb.text,
                    id: idValue,
                    to: crumb.to || { name: routeRecord.name, params: to.params },
                };

                finalCrumbs.push(existing || newCrumb);

                if (!existing)
                    breadcrumbStore.setTextItemAuto(newCrumb.text, newCrumb.id, routeRecord.meta?.parent);

                if (result?.parentParams)
                    paramsForParent = result.parentParams;
            } else {
                const staticCrumbs = (Array.isArray(breadcrumbMeta) ? breadcrumbMeta : [breadcrumbMeta]);
                finalCrumbs.push(...staticCrumbs);
            }
        }

        const uniqueCrumbs = finalCrumbs.filter(
            (item, index, self) =>
                index === self.findIndex(
                    (t) => t.text === item.text || (t.id && t.id === item.id)
                )
        );

        breadcrumbStore.setBase(uniqueCrumbs);
    }

    
    const { isUserAuthenticated } = useAuth();
    if (from.name === to?.name) {
        return next();
    }
    const isUserLoggedIn = await isUserAuthenticated();
    if (isUserLoggedIn) {
        if (to?.name === 'login') {
            return next({ name: 'start' });
        }
    } else {
        if (to?.name !== 'login') {
            return next({ name: 'login' });
        }
    }

    return next();
});

export default router;
