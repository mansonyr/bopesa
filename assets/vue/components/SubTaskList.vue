<template>
  <div class="subtask-list">
    <div v-for="subtask in subtasks" :key="subtask.id" class="subtask-item mb-2">
      <div class="d-flex align-items-center">
        <div class="form-check">
          <input 
            class="form-check-input" 
            type="checkbox" 
            :checked="subtask.isCompleted"
            @change="toggleSubTask(subtask)"
          >
        </div>
        <div class="flex-grow-1 ms-2">
          <div class="d-flex justify-content-between align-items-start">
            <span :class="{ 'text-decoration-line-through': subtask.isCompleted }">
              {{ subtask.title }}
            </span>
            <div class="dropdown">
              <button class="btn btn-link btn-sm p-0" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item" @click="editSubTask(subtask)">Modifier</button></li>
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item text-danger" @click="deleteSubTask(subtask)">Supprimer</button></li>
              </ul>
            </div>
          </div>
          <small class="text-muted" v-if="subtask.description">{{ subtask.description }}</small>
          <small class="text-muted d-block" v-if="subtask.resources">
            <i class="bi bi-link-45deg"></i> {{ subtask.resources }}
          </small>
        </div>
      </div>
    </div>

    <!-- Modal pour créer/modifier une sous-tâche -->
    <div class="modal fade" id="subtaskModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Modifier la sous-tâche' : 'Nouvelle sous-tâche' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveSubTask">
              <div class="mb-3">
                <label class="form-label">Titre</label>
                <input type="text" class="form-control" v-model="subtaskForm.title" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" v-model="subtaskForm.description" rows="2"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Ressources</label>
                <input type="text" class="form-control" v-model="subtaskForm.resources" placeholder="URL ou référence">
              </div>
              <div class="text-end">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import axios from 'axios';

export default {
  name: 'SubTaskList',
  props: {
    subtasks: {
      type: Array,
      required: true
    }
  },
  emits: ['subtask-updated', 'subtask-deleted'],
  setup(props, { emit }) {
    const subtaskForm = ref({
      title: '',
      description: '',
      resources: '',
      isCompleted: false
    });
    const isEditing = ref(false);
    const editingSubTaskId = ref(null);

    const toggleSubTask = async (subtask) => {
      try {
        const updatedSubTask = {
          ...subtask,
          isCompleted: !subtask.isCompleted
        };
        await axios.put(`/api/subtasks/${subtask.id}`, updatedSubTask);
        emit('subtask-updated', updatedSubTask);
      } catch (error) {
        console.error('Erreur lors de la mise à jour de la sous-tâche:', error);
      }
    };

    const editSubTask = (subtask) => {
      isEditing.value = true;
      editingSubTaskId.value = subtask.id;
      subtaskForm.value = {
        title: subtask.title,
        description: subtask.description,
        resources: subtask.resources,
        isCompleted: subtask.isCompleted
      };
      const modal = new bootstrap.Modal(document.getElementById('subtaskModal'));
      modal.show();
    };

    const saveSubTask = async () => {
      try {
        let response;
        if (isEditing.value) {
          response = await axios.put(`/api/subtasks/${editingSubTaskId.value}`, subtaskForm.value);
        } else {
          response = await axios.post('/api/subtasks', subtaskForm.value);
        }
        emit('subtask-updated', response.data.subtask);
        const modal = bootstrap.Modal.getInstance(document.getElementById('subtaskModal'));
        modal.hide();
      } catch (error) {
        console.error('Erreur lors de l\'enregistrement de la sous-tâche:', error);
      }
    };

    const deleteSubTask = async (subtask) => {
      if (confirm('Êtes-vous sûr de vouloir supprimer cette sous-tâche ?')) {
        try {
          await axios.delete(`/api/subtasks/${subtask.id}`);
          emit('subtask-deleted', subtask.id);
        } catch (error) {
          console.error('Erreur lors de la suppression de la sous-tâche:', error);
        }
      }
    };

    return {
      subtaskForm,
      isEditing,
      toggleSubTask,
      editSubTask,
      saveSubTask,
      deleteSubTask
    };
  }
};
</script>

<style scoped>
.subtask-item {
  font-size: 0.9rem;
}

.form-check-input:checked ~ .flex-grow-1 {
  opacity: 0.6;
}
</style>
