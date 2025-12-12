// src/plugins/heroicons.js
import { defineAsyncComponent } from 'vue';

const icons = {
  // Navigation & General UI
  HomeIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/HomeIcon')),
  Bars3Icon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/Bars3Icon')),
  MagnifyingGlassIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/MagnifyingGlassIcon')),
  AdjustmentsVerticalIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/AdjustmentsVerticalIcon')),
  EllipsisVerticalIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/EllipsisVerticalIcon')),
  ChevronDownIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ChevronDownIcon')),
  ArrowLeftIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ArrowLeftIcon')),
  ArrowUpRightIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ArrowUpRightIcon')),
  InformationCircleIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/InformationCircleIcon')),

  // Actions
  PencilIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/PencilIcon')),
  PencilSquareIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/PencilSquareIcon')),
  TrashIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/TrashIcon')),
  EyeIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/EyeIcon')),
  ArrowUpTrayIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ArrowUpTrayIcon')),
  ArrowDownTrayIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ArrowDownTrayIcon')),
  ArrowPathIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ArrowPathIcon')),
  SquaresPlusIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/SquaresPlusIcon')),
  UserPlusIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/UserPlusIcon')),

  // Objects & Documents
  FolderIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/FolderIcon')),
  DocumentIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/DocumentIcon')),
  DocumentTextIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/DocumentTextIcon')),
  DocumentArrowDownIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/DocumentArrowDownIcon')),
  NewspaperIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/NewspaperIcon')),
  PaperClipIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/PaperClipIcon')),
  ArchiveBoxIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ArchiveBoxIcon')),
  BookmarkIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/BookmarkIcon')),
  BookmarkSquareIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/BookmarkSquareIcon')),
  ClipboardDocumentListIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ClipboardDocumentListIcon')),
  ClipboardDocumentCheckIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ClipboardDocumentCheckIcon')),

  // Users & Roles
  UserIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/UserIcon')),
  UsersIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/UsersIcon')),
  UserGroupIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/UserGroupIcon')),
  UserCircleIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/UserCircleIcon')),
  ShieldCheckIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ShieldCheckIcon')),
  KeyIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/KeyIcon')),

  // Academic & Data
  AcademicCapIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/AcademicCapIcon')),
  BookOpenIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/BookOpenIcon')),
  BuildingOfficeIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/BuildingOfficeIcon')),
  RectangleGroupIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/RectangleGroupIcon')),
  PresentationChartLineIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/PresentationChartLineIcon')),
  ChartBarIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ChartBarIcon')),
  BeakerIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/BeakerIcon')),
  GlobeEuropeAfricaIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/GlobeEuropeAfricaIcon')),

  // Time & Status
  CalendarIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/CalendarIcon')),
  ClockIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ClockIcon')),
  CheckCircleIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/CheckCircleIcon')),
  ExclamationTriangleIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ExclamationTriangleIcon')),
  CalendarDaysIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/CalendarDaysIcon')),
  DocumentArrowUpIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/DocumentArrowUpIcon')),
  ChatBubbleBottomCenterTextIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ChatBubbleBottomCenterTextIcon')),
  XCircleIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/XCircleIcon')),
  ArrowDownOnSquareIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/ArrowDownOnSquareIcon')),
  CurrencyDollarIcon: defineAsyncComponent(() => import('@heroicons/vue/24/outline/CurrencyDollarIcon')),
};

export function registerHeroIcons(app) {
  for (const [name, icon] of Object.entries(icons)) {
    app.component(name, icon);
  }
}
