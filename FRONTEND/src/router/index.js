import AppLayout from '@/Layouts/AppLayout.vue';
import Categories from '@/pages/Categories.vue';
import Customers from '@/pages/Customers.vue';
import Dashboard from '@/pages/Dashboard.vue';
import Inventory from '@/pages/Inventory.vue';
import Login from '@/pages/Login.vue';
import Products from '@/pages/Products.vue';
import Purchases from '@/pages/Purchases.vue';
import Receipt from '@/pages/Receipt.vue';
import Reports from '@/pages/Reports.vue';
import Sales from '@/pages/Sales.vue';
import Suppliers from '@/pages/Suppliers.vue';
import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {path: '/login', name: 'login', component: Login},
        {path: '/', component: AppLayout, children: [
            {name: 'home', path: 'home', component: Dashboard},
            {name: 'products', path: 'products', component: Products},
            {name: 'categories', path: 'categories', component: Categories},
            {name: 'suppliers', path: 'suppliers', component: Suppliers},
            {name: 'customers', path: 'customers', component: Customers},
            {name: 'purchases', path: 'purchases', component: Purchases},
            {name: 'sales', path: 'sales', component: Sales},
            {name: 'reports', path: 'reports', component: Reports},
            {name: 'receipt', path: 'receipt/:id', component: Receipt},
            {name: 'inventory', path: 'inventory', component: Inventory},
        ]}
    ]
});

export default router;