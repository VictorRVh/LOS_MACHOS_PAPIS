<script setup>
import { ref, onMounted, computed } from 'vue';
import { RouterLink } from 'vue-router';
import {
  ClockIcon,
  ArchiveBoxXMarkIcon,
} from '@heroicons/vue/24/outline';
import Button from '@/components/ui/Button.vue';
import useHttpRequest from '@/composables/useHttpRequest';
import { formatDistanceToNow } from 'date-fns';
import { es } from 'date-fns/locale';

const { loading, index: fetchAllNotifications } = useHttpRequest('/actividades-recientes/all');
const allNotifications = ref([]);

const unreadNotifications = computed(() => allNotifications.value.filter(n => !n.isRead));
const readNotifications = computed(() => allNotifications.value.filter(n => n.isRead));

const loadNotifications = async () => {
    const data = await fetchAllNotifications();
    if (Array.isArray(data)) {
        const unreadCount = 2;
        allNotifications.value = data.map((actividad, index) => ({
            id: actividad.id,
            icon: ClockIcon,
            text: actividad.descripcion,
            time: formatDistanceToNow(new Date(actividad.fecha), { addSuffix: true, locale: es }),
            isRead: index >= unreadCount,
            route: { name: 'notificaciones.index' }
        }));
    }
};

const markAllAsRead = () => {
  allNotifications.value.forEach(n => n.isRead = true);
};

const clearRead = () => {
  allNotifications.value = allNotifications.value.filter(n => !n.isRead);
};

onMounted(() => {
  loadNotifications();
});
</script>