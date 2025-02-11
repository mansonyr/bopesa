<template>
  <div class="app-container">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">Bopesa Marketing</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <router-link class="nav-link" to="/">Tableau de bord</router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/channels">Canaux</router-link>
            </li>
          </ul>
          <div class="d-flex">
            <button class="btn btn-light" @click="createChannel">
              <i class="bi bi-plus-lg"></i> Nouveau Canal
            </button>
          </div>
        </div>
      </div>
    </nav>

    <div class="container mt-5 pt-5">
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
import { useRouter, onMounted } from 'vue-router';
import mitt from 'mitt';

// Créer un event bus global
export const emitter = mitt();

export default {
  name: 'App',
  setup() {
    const router = useRouter();

    const createChannel = async () => {
      await router.push('/channels');
      emitter.emit('open-channel-modal');
    };

    return {
      createChannel
    };
  }
}
</script>

<style lang="scss" scoped>
.display-4 {
  color: var(--primary-color);
  margin-bottom: 1rem;
}

.lead {
  color: var(--secondary-color);
}</style>
