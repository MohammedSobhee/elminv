<template>
  <div class="mb-5">
    <div class="card">
      <div class="card-header">
        <slot></slot>
      </div>
      <ul class="list-group list-group-flush">
        <li class="list-group-item" :key="todo.id" v-for="todo in todos">
          <a
            v-if="todo.answer"
            :class="{ completed: todo.answer }"
            :href="todo.link">
            <i class="icon-completed fa fa-check"></i>
            <strong v-if="todo.grade">(Grade: {{ todo.grade }})</strong>
            {{ todo.text }}
          </a>
          <component
            v-else
            class="todo"
            :class="{ assigned: todo.assigned }"
            :is="todo.assigned ? 'a' : 'span'"
            :href="todo.link || ''">
            <i class="icon-todo fa fa-times"></i>
            <strong v-if="todo.assigned">(Assigned)</strong>
            {{ todo.text }}
          </component>
        </li>
      </ul>
    </div>
    <div v-show="completedTasks.length" class="completed">
      <small>Completed: {{ completedTasks.length }} tasks!</small>
    </div>
  </div>
</template>

<script>
export default {
    name: 'DashboardTodo',
    props: { type: String },
    data() {
        return {
            // Fake data in place of axios/james
            todosPersonal: [
                {
                    id: 0,
                    text: 'Learn Vue',
                    link: '#',
                    answer: 223
                },
                {
                    id: 1,
                    text: 'Team worksheet here 1',
                    link: '#',
                    answer: 442,
                    grade: 98
                },
                {
                    id: 2,
                    text: 'Team worksheet here 2',
                    link: '#',
                    answer: 0,
                    assigned: 1
                },
                {
                    id: 3,
                    text: 'Another worksheet',
                    link: '#',
                    answer: 0
                }
            ],
            todosTeam: [
                {
                    id: 0,
                    text: 'Team worksheet here 5',
                    link: '#',
                    answer: 0
                },
                {
                    id: 1,
                    text: 'Team worksheet here 6',
                    link: '#',
                    answer: 442
                },
                {
                    id: 2,
                    text: 'Team worksheet here 7',
                    link: '#',
                    answer: 0
                },
                {
                    id: 3,
                    text: 'Team worksheet here 8',
                    link: '#',
                    answer: 0
                }
            ]
        };
    },
    computed: {
        completedTasks() {
            return this.todos.filter(todo => todo.answer).map(todo => todo.id);
        },
        todos() {
            if (this.type === 'team')
                return this.todosTeam.filter(todo => todo.assigned || todo.answer);
            else
                return this.todosPersonal.filter(todo => todo.assigned || todo.answer);
        }
    }
};
</script>

<style lang="scss" scoped>
.icon-completed {
  color: $secondary;
}
.completed {
  color: $dark-secondary;
}
.todo {
  color: $medium-gray;
}
.assigned {
  color: $black;
}
</style>
