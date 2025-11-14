import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import Graphs from '../views/Graphs.vue';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
  },
  {
    path: '/graphs',
    name: 'Graphs',
    component: Graphs,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
