import { createRouter, createWebHistory } from 'vue-router';
import ChannelList from './components/ChannelList.vue';

const routes = [
  {
    path: '/',
    redirect: '/channels'
  },
  {
    path: '/channels',
    name: 'channels',
    component: ChannelList
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
