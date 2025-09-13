// resources/js/src/store/useLayoutStore.js

import { defineStore } from 'pinia';

export const useLayoutStore = defineStore('layout', {
    state: () => ({
        // Controla si el sidebar está colapsado o expandido
        isSidebarCollapsed: false,
        // Título que se mostrará en el PageHeader
        pageTitle: 'Inicio',
        // Controla el estado de carga para el Suspense
        isPageLoading: false,
    }),
    actions: {
        toggleSidebar() {
            this.isSidebarCollapsed = !this.isSidebarCollapsed;
        },
        setPageTitle(newTitle) {
            this.pageTitle = newTitle;
        },
        // Estas acciones reemplazarán el ref 'asyncLoading' en tus layouts
        setPageLoading(status) {
            this.isPageLoading = status;
        }
    },
});