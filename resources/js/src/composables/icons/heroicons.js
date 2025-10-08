// src/plugins/heroicons.js
import { NewspaperIcon, PaperClipIcon } from '@heroicons/vue/24/outline';
import { defineAsyncComponent } from 'vue';

// Importa los íconos en estilo outline
const icons = {
  HomeIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/HomeIcon')),
  BeakerIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/BeakerIcon')),
  UserIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/UserIcon')),
  AcademicCapIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/AcademicCapIcon')),
  UsersIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/UsersIcon')),
  UserGroupIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/UserGroupIcon')),
  BookOpenIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/BookOpenIcon')),
  BuildingOfficeIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/BuildingOfficeIcon')),
  PresentationChartLineIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/PresentationChartLineIcon')),
  ChartBarIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ChartBarIcon')),
  FolderIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/FolderIcon')),
  BookmarkIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/BookmarkIcon')),
  BookmarkSquareIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/BookmarkSquareIcon')),
  EyeIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/EyeIcon')),
  PencilSquareIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/PencilSquareIcon')),
  TrashIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/TrashIcon')),
  CalendarIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/CalendarIcon')),
  ClipboardDocumentListIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/ClipboardDocumentListIcon')),
  PaperClipIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/PaperClipIcon')),
  NewspaperIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/NewspaperIcon')), 
  DocumentIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/DocumentIcon')), 
  DocumentArrowDownIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/DocumentArrowDownIcon')), 
  // DownloadIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/DownloadIcon')),
  BookOpenIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/BookOpenIcon')),
  Bars3Icon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/Bars3Icon')),
  // Agrega más íconos aquí según sea necesario

  ShieldCheckIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/ShieldCheckIcon')), 
  KeyIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/KeyIcon')),
  GlobeEuropeAfricaIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/GlobeEuropeAfricaIcon')),
  UserCircleIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/UserCircleIcon')),
  ArrowLeftIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/ArrowLeftIcon')),
  MagnifyingGlassIcon:  defineAsyncComponent( () => import('@heroicons/vue/24/outline/MagnifyingGlassIcon')),
  AdjustmentsVerticalIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/AdjustmentsVerticalIcon')),
  PencilIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/PencilIcon')),
  ArrowDownTrayIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/ArrowDownTrayIcon')),
  CalendarDateRangeIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/CalendarDateRangeIcon')),
  PresentationChartLineIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/PresentationChartLineIcon')),
  AcademicCapIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/AcademicCapIcon')),
  SquaresPlusIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/SquaresPlusIcon')),
  RectangleGroupIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/RectangleGroupIcon')),
  NewspaperIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/NewspaperIcon')),
  ClockIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/ClockIcon')),
  CheckCircleIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/CheckCircleIcon')),
  ArrowUpTrayIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/ArrowUpTrayIcon')),
  ChevronDownIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/ChevronDownIcon')),
  ArchiveBoxIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/ArchiveBoxIcon')),
  ArrowPathIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/ArrowPathIcon')),
  EyeIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/EyeIcon')),
  DocumentTextIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/DocumentTextIcon')),
  ArrowUpRightIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/ArrowUpRightIcon')),
  ClipboardDocumentCheckIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/ClipboardDocumentCheckIcon')),
  UserPlusIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/UserPlusIcon')),
  InformationCircleIcon: defineAsyncComponent( () => import('@heroicons/vue/24/outline/InformationCircleIcon')),
  ///___________________________________

};

// Registra los íconos como componentes globales
export function registerHeroIcons(app) {
  for (const [name, icon] of Object.entries(icons)) {
    app.component(name, icon);
  }
}
