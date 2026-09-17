import AppLayout from '@/Layouts/AppLayout.vue';
import Categories from '@/pages/Categories.vue';
import Home from '@/pages/Home.vue';
import Login from '@/pages/Login.vue';
import Products from '@/pages/Products.vue';
import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {path: '/login', name: 'login', component: Login},
        {path: '/', component: AppLayout, children: [
            {name: 'home', path: 'home', component: Home},
            {name: 'products', path: 'products', component: Products},
            {name: 'categories', path: 'categories', component: Categories},
        ]}
    ]
});

export default router;