import { defineStore } from 'pinia';

export const useLayoutStore = defineStore('layout', {
    state: () => ({
        isSidebarCollapsed: false,
        isSidebarOpenMobile: false,
        pageTitle: 'Inicio',
        isPageLoading: false,
    }),
    actions: {
        toggleSidebar() {
            this.isSidebarCollapsed = !this.isSidebarCollapsed;
        },
        toggleSidebarMobile() {
            this.isSidebarOpenMobile = !this.isSidebarOpenMobile;
        },
        setPageTitle(newTitle) {
            this.pageTitle = newTitle;
        },
        setPageLoading(status) {
            this.isPageLoading = status;
        }
    },
});