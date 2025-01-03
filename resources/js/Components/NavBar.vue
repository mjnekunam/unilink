<script setup>
import {
    Disclosure,
    DisclosureButton,
    DisclosurePanel,
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
} from "@headlessui/vue";
import { Bars3Icon, BellIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import ApplicationLogo from "./ApplicationLogo.vue";

const props = defineProps({
    user: {
        type: Object,
    },
    active: {
        type: Boolean,
    },
});

const isGuest = () => {
    return props.user === null ? true : false;
};

const classes = computed(() => [
    props.active
        ? "bg-gray-900 text-white transition duration-150 ease-in-out"
        : "text-gray-300 hover:bg-gray-700 hover:text-white transition duration-150 ease-in-out",
    "rounded-md px-3 py-2 text-sm font-medium transition duration-150 ease-in-out",
]);

const navigation = [
    {
        name: "خانه",
        href: route("home"),
        current: route().current("home"),
    },
    {
        name: "تماس با ما",
        href: "#",
        current: false,
    },
];
</script>

<template>
    <Disclosure as="nav" class="bg-gray-800" v-slot="{ open }">
        <div class="px-2 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="relative flex items-center justify-between h-16">
                <div class="absolute inset-y-0 flex items-center r-0 sm:hidden">
                    <!-- Mobile menu button-->
                    <DisclosureButton
                        class="relative inline-flex items-center justify-center p-2 text-gray-400 rounded-md hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                    >
                        <span class="absolute -inset-0.5" />
                        <span class="sr-only">Open main menu</span>
                        <Bars3Icon
                            v-if="!open"
                            class="block w-6 h-6"
                            aria-hidden="true"
                        />
                        <XMarkIcon
                            v-else
                            class="block w-6 h-6"
                            aria-hidden="true"
                        />
                    </DisclosureButton>
                </div>
                <div
                    class="flex items-center justify-center flex-1 sm:items-stretch sm:justify-start"
                >
                    <div class="flex items-center shrink-0">
                        <ApplicationLogo class="block w-auto h-8" />
                    </div>
                    <div class="hidden sm:ml-6 sm:block">
                        <div class="flex mr-5 space-x-4">
                            <Link
                                v-for="item in navigation"
                                :key="item.name"
                                :href="item.href"
                                :class="[
                                    item.current
                                        ? 'bg-gray-900 text-white'
                                        : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                                    'rounded-md px-3 py-2 text-sm font-medium',
                                ]"
                                :aria-current="
                                    item.current ? 'page' : undefined
                                "
                                >{{ item.name }}
                            </Link>
                        </div>
                    </div>
                </div>
                <div
                    class="absolute inset-y-0 left-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0"
                >
                    <Link
                        :href="route('register')"
                        :class="classes"
                        class="bg-blue-500"
                        :active="false"
                        v-if="isGuest()"
                    >
                        ثبت نام
                    </Link>
                    <Link
                        :href="route('login')"
                        :class="classes"
                        :active="false"
                        v-if="isGuest()"
                    >
                        ورود
                    </Link>
                    <button
                        type="button"
                        class="relative p-1 text-gray-400 bg-gray-800 rounded-full hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                    >
                        <span class="absolute -inset-1.5" />
                        <span class="sr-only">View notifications</span>
                        <BellIcon class="w-6 h-6" aria-hidden="true" />
                    </button>

                    <!-- Profile dropdown -->
                    <Menu as="div" class="relative mr-3">
                        <div>
                            <MenuButton
                                class="relative flex text-sm bg-gray-800 rounded-full focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                            >
                                <span class="absolute -inset-1.5" />
                                <span class="sr-only">Open user menu</span>
                                <img
                                    v-if="!isGuest()"
                                    class="w-8 h-8 rounded-full"
                                    :src="user.avatar"
                                    alt="avatar"
                                />
                            </MenuButton>
                        </div>
                        <transition
                            enter-active-class="transition duration-100 ease-out"
                            enter-from-class="transform scale-95 opacity-0"
                            enter-to-class="transform scale-100 opacity-100"
                            leave-active-class="transition duration-75 ease-in"
                            leave-from-class="transform scale-100 opacity-100"
                            leave-to-class="transform scale-95 opacity-0"
                        >
                            <MenuItems
                                class="absolute left-0 z-10 w-48 py-1 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                            >
                                <MenuItem v-slot="{ active }">
                                    <Link
                                        :href="route('dashboard')"
                                        :class="[
                                            active
                                                ? 'bg-gray-100 outline-none'
                                                : '',
                                            'block px-4 py-2 text-sm text-gray-700',
                                        ]"
                                    >
                                        داشبورد</Link
                                    >
                                </MenuItem>
                                <MenuItem v-slot="{ active }">
                                    <Link
                                        :href="route('logout')"
                                        method="post"
                                        :class="[
                                            active
                                                ? 'bg-gray-100 outline-none'
                                                : '',
                                            'block px-4 py-2 text-sm text-gray-700',
                                        ]"
                                    >
                                        خروج</Link
                                    >
                                </MenuItem>
                            </MenuItems>
                        </transition>
                    </Menu>
                </div>
            </div>
        </div>

        <DisclosurePanel class="sm:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <DisclosureButton
                    v-for="item in navigation"
                    :key="item.name"
                    as="a"
                    :href="item.href"
                    :class="[
                        item.current
                            ? 'bg-gray-900 text-white'
                            : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                        'block rounded-md px-3 py-2 text-base font-medium',
                    ]"
                    :aria-current="item.current ? 'page' : undefined"
                    >{{ item.name }}</DisclosureButton
                >
            </div>
        </DisclosurePanel>
    </Disclosure>
</template>
