import { createRouter, createWebHistory } from 'vue-router';
import routes from './routes';
import useAuth from '../composables/useAuth';
import { useBreadcrumbStore } from '@/store/useBreadcrumbStore';

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    // Lógica para el Breadcrumb
    const breadcrumbStore = useBreadcrumbStore();
    const { breadcrumb } = to.meta;
    let breadcrumbItems = [];

    if (breadcrumb) {
        if (typeof breadcrumb === 'string') {
            breadcrumbItems = [{ text: breadcrumb }];
        } else if (Array.isArray(breadcrumb)) {
            breadcrumbItems = breadcrumb.map(item => ({
                text: item.name,
                to: item.to,
            }));
        }
    }
    breadcrumbStore.setItems(breadcrumbItems);

    const { isUserAuthenticated } = useAuth();

    if (from.name === to.name) {
        return next();
    }

    const isUserLoggedIn = await isUserAuthenticated();

    if (isUserLoggedIn) {
        if (to.name === 'login') {
            return next({ name: 'users' });
        }
    } else {
        if (to.name !== 'login') {
            return next({ name: 'login' });
        }
    }
    
    return next();
});

export default router;