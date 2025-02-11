<template>
  <div class="task-list">
    <div v-for="task in tasks" :key="task.id" class="task-item mb-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <h6 class="card-title mb-2">{{ task.title }}</h6>
            <div class="dropdown">
              <button class="btn btn-link btn-sm p-0" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item" @click="editTask(task)">Modifier</button></li>
                <li><button class="dropdown-item" @click="createSubTask(task)">Nouvelle sous-tâche</button></li>
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item text-danger" @click="deleteTask(task)">Supprimer</button></li>
              </ul>
            </div>
          </div>
          <p class="card-text small mb-2">{{ task.description }}</p>
          <div class="progress mb-2" style="height: 5px;">
            <div class="progress-bar" role="progressbar" :style="{ width: task.progress + '%' }"></div>
          </div>
          <div class="subtask-list">
            <sub-task-list 
              :subtasks="task.subTasks" 
              @subtask-updated="updateSubTask" 
              @subtask-deleted="deleteSubTask"
            ></sub-task-list>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal pour créer/modifier une tâche -->
    <div class="modal fade" id="taskModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Modifier la tâche' : 'Nouvelle tâche' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveTask">
              <div class="mb-3">
                <label class="form-label">Titre</label>
                <input type="text" class="form-control" v-model="taskForm.title" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" v-model="taskForm.description" rows="3"></textarea>
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
import SubTaskList from './SubTaskList.vue';
import axios from 'axios';

export default {
  name: 'TaskList',
  components: {
    SubTaskList
  },
  props: {
    tasks: {
      type: Array,
      required: true
    }
  },
  emits: ['task-updated', 'task-deleted'],
  setup(props, { emit }) {
    const taskForm = ref({
      title: '',
      description: ''
    });
    const isEditing = ref(false);
    const editingTaskId = ref(null);

    const editTask = (task) => {
      isEditing.value = true;
      editingTaskId.value = task.id;
      taskForm.value = {
        title: task.title,
        description: task.description
      };
      const modalEl = document.getElementById('taskModal');
      const modal = Modal.getOrCreateInstance(modalEl);
      modal.show();
    };

    const saveTask = async () => {
      try {
        let response;
        if (isEditing.value) {
          response = await axios.put(`/api/tasks/${editingTaskId.value}`, taskForm.value);
        } else {
          response = await axios.post('/api/tasks', taskForm.value);
        }
        emit('task-updated', response.data.task);
        const modalEl = document.getElementById('taskModal');
        const modal = Modal.getOrCreateInstance(modalEl);
        modal.hide();
      } catch (error) {
        console.error('Erreur lors de l\'enregistrement de la tâche:', error);
      }
    };

    const deleteTask = async (task) => {
      if (confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')) {
        try {
          await axios.delete(`/api/tasks/${task.id}`);
          emit('task-deleted', task.id);
        } catch (error) {
          console.error('Erreur lors de la suppression de la tâche:', error);
        }
      }
    };

    const updateSubTask = async (subtask) => {
      try {
        await axios.put(`/api/subtasks/${subtask.id}`, subtask);
        // La mise à jour de la progression de la tâche est gérée par le backend
      } catch (error) {
        console.error('Erreur lors de la mise à jour de la sous-tâche:', error);
      }
    };

    const deleteSubTask = async (subtaskId) => {
      try {
        await axios.delete(`/api/subtasks/${subtaskId}`);
        // La mise à jour de la progression de la tâche est gérée par le backend
      } catch (error) {
        console.error('Erreur lors de la suppression de la sous-tâche:', error);
      }
    };

    return {
      taskForm,
      isEditing,
      editTask,
      saveTask,
      deleteTask,
      updateSubTask,
      deleteSubTask
    };
  }
};
</script>

<style scoped>
.task-item .card {
  background-color: #f8f9fa;
}

.task-item .progress {
  background-color: #e9ecef;
}

.subtask-list {
  margin-top: 10px;
  padding-left: 15px;
  border-left: 2px solid #dee2e6;
}
</style>
