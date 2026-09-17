import Home from '@/pages/Home.vue';
import Login from '@/pages/Login.vue';
import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {path: '/home', name: 'home', component: Home},
        {path: '/', name: 'login', component: Login}
    ]
});

export default router;