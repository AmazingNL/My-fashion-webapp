/** @format */

import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import ProductsView from "../views/ProductsView.vue";
import ProductDetailsView from "../views/ProductDetailsView.vue";
import CartView from "../views/CartView.vue";
import CheckoutView from "../views/CheckoutView.vue";
import OrdersView from "../views/OrdersView.vue";
import LoginView from "../views/LoginView.vue";
import RegisterView from "../views/RegisterView.vue";
import SettingsView from "../views/SettingsView.vue";
import AdminDashboardView from "../views/AdminDashboardView.vue";
import AdminProductsView from "../views/AdminProductsView.vue";
import AdminOrdersView from "../views/AdminOrdersView.vue";
import AdminUsersView from "../views/AdminUsersView.vue";
import AdminAppointmentsView from "../views/AdminAppointmentsView.vue";
import AdminEmailsView from "../views/AdminEmailsView.vue";

const routes = [
	{
		path: "/",
		component: HomeView,
	},
	{
		path: "/products",
		component: ProductsView,
	},
	{
		path: "/products/:id",
		component: ProductDetailsView,
	},
	{
		path: "/cart",
		component: CartView,
	},
	{
		path: "/checkout",
		component: CheckoutView,
	},
	{
		path: "/orders",
		component: OrdersView,
	},
	{
		path: "/login",
		component: LoginView,
	},
	{
		path: "/register",
		component: RegisterView,
	},
	{
		path: "/settings",
		component: SettingsView,
	},
	{
		path: "/admin",
		component: AdminDashboardView,
	},
	{
		path: "/admin/products",
		component: AdminProductsView,
	},
	{
		path: "/admin/orders",
		component: AdminOrdersView,
	},
	{
		path: "/admin/users",
		component: AdminUsersView,
	},
	{
		path: "/admin/appointments",
		component: AdminAppointmentsView,
	},
	{
		path: "/admin/emails",
		component: AdminEmailsView,
	},
];

export default createRouter({
	history: createWebHistory(),
	routes,
});
