import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '../composables/useAuth';
import Graphs from '../views/Graphs.vue';
import Login from '../views/Login.vue';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { guest: true },
  },
  {
    path: '/',
    name: 'Graphs',
    component: Graphs,
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guard для проверки авторизации
router.beforeEach(async (to, from, next) => {
  const { isAuthenticated, fetchUser } = useAuth();

  // Пытаемся получить пользователя при первой загрузке
  if (isAuthenticated.value === false && to.meta.requiresAuth) {
    await fetchUser();
  }

  // Если требуется авторизация и пользователь не авторизован
  if (to.meta.requiresAuth && !isAuthenticated.value) {
    next({ name: 'Login' });
  }
  // Если пользователь авторизован и пытается зайти на страницу логина
  else if (to.meta.guest && isAuthenticated.value) {
    next({ name: 'Graphs' });
  }
  // Во всех остальных случаях разрешаем переход
  else {
    next();
  }
});

export default router;
