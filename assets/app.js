// Import Bootstrap first
import 'bootstrap';
import 'bootstrap-icons/font/bootstrap-icons.css';

import './bootstrap.js';
import './styles/app.scss';
import { createApp } from 'vue';
import App from './vue/App.vue';
import router from './vue/router.js';
import axios from 'axios';

// Configure Axios
axios.defaults.baseURL = window.location.origin;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add request interceptor
axios.interceptors.request.use(function (config) {
  console.log('Making request to:', config.method.toUpperCase(), config.url);
  console.log('Request data:', config.data);
  return config;
});

// Add response interceptor
axios.interceptors.response.use(function (response) {
  console.log('Response from:', response.config.method.toUpperCase(), response.config.url);
  console.log('Response data:', response.data);
  return response;
}, function (error) {
  console.error('Request failed:', error);
  return Promise.reject(error);
});

// Start Vue application
const app = createApp(App);
app.use(router);
app.mount('#app');
