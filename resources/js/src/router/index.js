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
    if (to.name) {
        const finalCrumbs = [];
        const allRoutes = router.getRoutes();
        let currentRouteRecord = allRoutes.find(r => r.name === to.name);
        let paramsForParent = { ...to.params };

        while (currentRouteRecord) {
            const breadcrumbMeta = currentRouteRecord.meta?.breadcrumb;
            if (breadcrumbMeta) {
                if (typeof breadcrumbMeta === 'function') {
                    const result = await breadcrumbMeta({ params: paramsForParent });
                    const crumb = result.crumb ? result.crumb : result;
                    finalCrumbs.unshift(crumb);
                    if (result.parentParams) {
                        paramsForParent = result.parentParams;
                    }
                } else {
                    const staticCrumbs = (Array.isArray(breadcrumbMeta) ? breadcrumbMeta : [breadcrumbMeta]).map(item => ({
                        text: item.name || item.text,
                        to: item.to,
                    }));
                    finalCrumbs.unshift(...staticCrumbs);
                }
            }
            const parentName = currentRouteRecord.meta?.parent;
            currentRouteRecord = parentName ? allRoutes.find(r => r.name === parentName) : null;
        }
        breadcrumbStore.setBase(finalCrumbs);
    }

    const { isUserAuthenticated } = useAuth();
    if (from.name === to.name) {
        return next();
    }
    const isUserLoggedIn = await isUserAuthenticated();
    if (isUserLoggedIn) {
        if (to.name === 'login') {
            return next({ name: 'start' });
        }
    } else {
        if (to.name !== 'login') {
            return next({ name: 'login' });
        }
    }
    
    return next();
});

export default router;