// src/composables/usePermissions.js
import { computed } from 'vue';
import useUserStore from '../store/useUserStore';

export default function usePermissions() {
  const userStore = useUserStore();

  const userPermissions = computed(() =>
    userStore.user?.permissions?.map(p => p.name) || []
  );

  const hasPermission = (requiredPermissions = []) => {
    return requiredPermissions.some(perm => userPermissions.value.includes(perm));
  };

  return {
    userPermissions,
    hasPermission
  };
}
