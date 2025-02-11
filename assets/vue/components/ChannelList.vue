<template>
  <div class="channel-list">
    <div class="row g-4">
      <div v-for="channel in channels" :key="channel.id" class="col-md-6 col-lg-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <h5 class="card-title">{{ channel.name }}</h5>
              <div class="dropdown">
                <button class="btn btn-link" type="button" data-bs-toggle="dropdown">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><button class="dropdown-item" @click="editChannel(channel)">Modifier</button></li>
                  <li><button class="dropdown-item" @click="createTask(channel)">Nouvelle tâche</button></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><button class="dropdown-item text-danger" @click="deleteChannel(channel)">Supprimer</button></li>
                </ul>
              </div>
            </div>
            <p class="card-text">{{ channel.description }}</p>
            <div class="progress mb-3">
              <div class="progress-bar" role="progressbar" :style="{ width: channel.progress + '%' }">
                {{ channel.progress }}%
              </div>
            </div>
            <div class="task-list">
              <task-list :tasks="channel.tasks" @task-updated="updateTask" @task-deleted="deleteTask"></task-list>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    &lt;!-- Modal pour créer/modifier un canal -->
    <div class="modal fade" id="channelModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Modifier le canal' : 'Nouveau canal' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Nom</label>
                <input type="text" class="form-control" v-model="channelForm.name" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" v-model="channelForm.description" rows="3"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
              <button type="button" class="btn btn-primary" @click="saveChannel">Enregistrer</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import TaskList from './TaskList.vue';
import axios from 'axios';
import { Modal } from 'bootstrap/dist/js/bootstrap.esm.js';
import { emitter } from '../App.vue';

export default {
  name: 'ChannelList',
  components: {
    TaskList
  },
  setup() {
    const channels = ref([]);
    const channelForm = ref({
      name: '',
      description: ''
    });
    const isEditing = ref(false);
    const editingChannelId = ref(null);

    const loadChannels = async () => {
      try {
        const response = await axios.get('/api/channels');
        channels.value = response.data.channels;
      } catch (error) {
        console.error('Erreur lors du chargement des canaux:', error);
      }
    };

    const createChannel = () => {
      isEditing.value = false;
      channelForm.value = { name: '', description: '' };
      const modalEl = document.getElementById('channelModal');
      const modal = Modal.getOrCreateInstance(modalEl);
      modal.show();
    };

    const editChannel = (channel) => {
      isEditing.value = true;
      editingChannelId.value = channel.id;
      channelForm.value = {
        name: channel.name,
        description: channel.description
      };
      const modalEl = document.getElementById('channelModal');
      const modal = Modal.getOrCreateInstance(modalEl);
      modal.show();
    };

    const saveChannel = async () => {
      console.log('saveChannel called');
      if (!channelForm.value.name) {
        console.log('Name is required');
        return;
      }
      
      console.log('Form data:', channelForm.value);
      try {
        if (isEditing.value) {
          console.log('Updating channel:', editingChannelId.value);
          const response = await axios.put(`/api/channels/${editingChannelId.value}`, channelForm.value);
          console.log('Update response:', response.data);
        } else {
          console.log('Creating new channel');
          const response = await axios.post('/api/channels', channelForm.value);
          console.log('Create response:', response.data);
        }
        await loadChannels();
        const modalEl = document.getElementById('channelModal');
        const modal = Modal.getOrCreateInstance(modalEl);
        modal.hide();
      } catch (error) {
        console.error('Erreur lors de l\'enregistrement du canal:', error);
      }
    };

    const deleteChannel = async (channel) => {
      if (confirm('Êtes-vous sûr de vouloir supprimer ce canal ?')) {
        try {
          await axios.delete(`/api/channels/${channel.id}`);
          await loadChannels();
        } catch (error) {
          console.error('Erreur lors de la suppression du canal:', error);
        }
      }
    };

    const updateTask = async (task) => {
      try {
        await axios.put(`/api/tasks/${task.id}`, task);
        await loadChannels();
      } catch (error) {
        console.error('Erreur lors de la mise à jour de la tâche:', error);
      }
    };

    const deleteTask = async (taskId) => {
      try {
        await axios.delete(`/api/tasks/${taskId}`);
        await loadChannels();
      } catch (error) {
        console.error('Erreur lors de la suppression de la tâche:', error);
      }
    };

    const createTask = async (channel) => {
      try {
        const response = await axios.post(`/api/tasks/channel/${channel.id}`, {
          title: 'Nouvelle tâche',
          description: 'Description de la nouvelle tâche'
        });
        await loadChannels();
      } catch (error) {
        console.error('Erreur lors de la création de la tâche:', error);
      }
    };

    onMounted(() => {
      loadChannels();
      // Écouter l'événement pour ouvrir le modal
      emitter.on('open-channel-modal', createChannel);
    });

    onUnmounted(() => {
      // Nettoyer l'écouteur d'événement
      emitter.off('open-channel-modal', createChannel);
    });

    return {
      channels,
      channelForm,
      isEditing,
      createChannel,
      editChannel,
      saveChannel,
      deleteChannel,
      createTask,
      updateTask,
      deleteTask
    };
  }
};
</script>

<style lang="scss" scoped>
.channel-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.channel-card {
  .card {
    transition: transform 0.2s, box-shadow 0.2s;
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
  }

  .dropdown-toggle::after {
    display: none;
  }

  .bi {
    font-size: 1.1rem;
  }
}
.channel-list {
  padding-top: 70px;
}

.card {
  transition: transform 0.2s;
}

.card:hover {
  transform: translateY(-5px);
}

.progress {
  height: 10px;
  border-radius: 5px;
}

.task-list {
  max-height: 300px;
  overflow-y: auto;
}
</style>
