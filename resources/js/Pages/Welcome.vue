<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const activeTab = ref('login');

const loginForm = useForm({
    email: '',
    password: '',
    remember: false,
});

const registerForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submitLogin = () => {
    loginForm.post('/login');
};

const submitRegister = () => {
    registerForm.post('/register');
};
</script>

<template>
    <Head title="FourFourTwo - پیش‌بینی لیگ برتر انگلیس">
        <link href="https://fonts.googleapis.com/css2?family=Tanha:wght@400;500;700&display=swap" rel="stylesheet">
    </Head>
    
    <div class="min-h-screen bg-white font-tanha" dir="rtl">
        <!-- Header -->
        <header class="border-b border-gray-200 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-black">FourFourTwo</h1>
                        <span class="mr-2 text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded">پیش‌بینی</span>
                    </div>
                    <div v-if="$page.props.auth.user" class="flex items-center space-x-4 space-x-reverse">
                        <Link 
                            :href="route('dashboard')"
                            class="text-black hover:text-gray-600 transition-colors font-medium"
                        >
                            داشبورد
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid lg:grid-cols-2 gap-16 items-start">
                <!-- Left Side - Content -->
                <div class="order-2 lg:order-1">
                    <div class="mb-12">
                        <h2 class="text-5xl lg:text-6xl font-bold text-black mb-6 leading-6">
                            پیش‌بینی لیگ برتر انگلیس
                        </h2>
                        <p class="text-xl text-gray-700 mb-8 leading-relaxed">
                            نتایج بازی‌های لیگ برتر را پیش‌بینی کنید. امتیاز کسب کنید. 
                            در جدول امتیازات صعود کنید.
                        </p>
                    </div>

                    <!-- Stats Preview -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-8">
                        <h3 class="text-lg font-bold text-black mb-4">چگونه کار می‌کند؟</h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-6 h-6 bg-black text-white rounded-full flex items-center justify-center text-sm font-bold mt-0.5">
                                    ۱
                                </div>
                                <div class="mr-3">
                                    <h4 class="font-medium text-black">پیش‌بینی کنید</h4>
                                    <p class="text-gray-600 text-sm">نتیجه هر بازی را قبل از شروع پیش‌بینی کنید</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-6 h-6 bg-black text-white rounded-full flex items-center justify-center text-sm font-bold mt-0.5">
                                    ۲
                                </div>
                                <div class="mr-3">
                                    <h4 class="font-medium text-black">امتیاز کسب کنید</h4>
                                    <p class="text-gray-600 text-sm">بر اساس دقت پیش‌بینی‌تان امتیاز دریافت کنید</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-6 h-6 bg-black text-white rounded-full flex items-center justify-center text-sm font-bold mt-0.5">
                                    ۳
                                </div>
                                <div class="mr-3">
                                    <h4 class="font-medium text-black">رقابت کنید</h4>
                                    <p class="text-gray-600 text-sm">در جدول امتیازات با دیگران رقابت کنید</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Prediction -->
                    <div class="border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm text-gray-500">نمونه پیش‌بینی</span>
                            <span class="text-sm text-gray-500">هفته ۱۵</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center text-xs font-bold">
                                    MUN
                                </div>
                                <span class="mr-3 font-medium text-black">منچستر یونایتد</span>
                            </div>
                            
                            <div class="flex items-center space-x-3 space-x-reverse">
                                <input 
                                    type="number" 
                                    value="2" 
                                    readonly
                                    class="w-12 h-10 border border-gray-300 rounded text-center text-black bg-white font-bold"
                                >
                                <span class="text-gray-400 font-bold">—</span>
                                <input 
                                    type="number" 
                                    value="1" 
                                    readonly
                                    class="w-12 h-10 border border-gray-300 rounded text-center text-black bg-white font-bold"
                                >
                            </div>
                            
                            <div class="flex items-center">
                                <span class="ml-3 font-medium text-black">لیورپول</span>
                                <div class="w-8 h-8 bg-red-700 text-white rounded-full flex items-center justify-center text-xs font-bold">
                                    LIV
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side - Auth Form -->
                <div class="order-1 lg:order-2">
                    <div class="bg-white border border-gray-200 rounded-lg p-8 sticky top-8">
                        <!-- Tab Navigation -->
                        <div class="flex mb-8 border-b border-gray-200">
                            <button 
                                @click="activeTab = 'login'"
                                :class="[
                                    'flex-1 py-3 px-4 text-base font-medium transition-colors border-b-2',
                                    activeTab === 'login' 
                                        ? 'text-black border-black' 
                                        : 'text-gray-500 border-transparent hover:text-black hover:border-gray-300'
                                ]"
                            >
                                ورود
                            </button>
                            <button 
                                @click="activeTab = 'register'"
                                :class="[
                                    'flex-1 py-3 px-4 text-base font-medium transition-colors border-b-2',
                                    activeTab === 'register' 
                                        ? 'text-black border-black' 
                                        : 'text-gray-500 border-transparent hover:text-black hover:border-gray-300'
                                ]"
                            >
                                ثبت‌نام
                            </button>
                        </div>
                        
                        <!-- Login Form -->
                        <form v-if="activeTab === 'login'" @submit.prevent="submitLogin" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-black mb-2">ایمیل</label>
                                <input 
                                    v-model="loginForm.email"
                                    type="email" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black transition-colors bg-white text-black"
                                    placeholder="example@email.com"
                                >
                                <div v-if="loginForm.errors.email" class="text-red-600 text-sm mt-1">{{ loginForm.errors.email }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-black mb-2">رمز عبور</label>
                                <input 
                                    v-model="loginForm.password"
                                    type="password" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black transition-colors bg-white text-black"
                                    placeholder="••••••••"
                                >
                                <div v-if="loginForm.errors.password" class="text-red-600 text-sm mt-1">{{ loginForm.errors.password }}</div>
                            </div>
                            
                            <div class="flex items-center">
                                <input 
                                    v-model="loginForm.remember"
                                    type="checkbox" 
                                    id="remember"
                                    class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black"
                                >
                                <label for="remember" class="mr-2 text-sm text-gray-700">مرا به خاطر بسپار</label>
                            </div>
                            
                            <button 
                                type="submit"
                                :disabled="loginForm.processing"
                                class="w-full bg-black hover:bg-gray-800 text-white font-medium py-3 px-4 rounded-lg transition-colors disabled:opacity-50"
                            >
                                <span v-if="loginForm.processing">در حال ورود...</span>
                                <span v-else>ورود</span>
                            </button>
                        </form>
                        
                        <!-- Register Form -->
                        <form v-if="activeTab === 'register'" @submit.prevent="submitRegister" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-black mb-2">نام</label>
                                <input 
                                    v-model="registerForm.name"
                                    type="text" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black transition-colors bg-white text-black"
                                    placeholder="نام شما"
                                >
                                <div v-if="registerForm.errors.name" class="text-red-600 text-sm mt-1">{{ registerForm.errors.name }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-black mb-2">ایمیل</label>
                                <input 
                                    v-model="registerForm.email"
                                    type="email" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black transition-colors bg-white text-black"
                                    placeholder="example@email.com"
                                >
                                <div v-if="registerForm.errors.email" class="text-red-600 text-sm mt-1">{{ registerForm.errors.email }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-black mb-2">رمز عبور</label>
                                <input 
                                    v-model="registerForm.password"
                                    type="password" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black transition-colors bg-white text-black"
                                    placeholder="••••••••"
                                >
                                <div v-if="registerForm.errors.password" class="text-red-600 text-sm mt-1">{{ registerForm.errors.password }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-black mb-2">تکرار رمز عبور</label>
                                <input 
                                    v-model="registerForm.password_confirmation"
                                    type="password" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black transition-colors bg-white text-black"
                                    placeholder="••••••••"
                                >
                            </div>
                            
                            <button 
                                type="submit"
                                :disabled="registerForm.processing"
                                class="w-full bg-black hover:bg-gray-800 text-white font-medium py-3 px-4 rounded-lg transition-colors disabled:opacity-50"
                            >
                                <span v-if="registerForm.processing">در حال ثبت‌نام...</span>
                                <span v-else>ثبت‌نام</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-gray-200 mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        © ۲۰۲۵ FourFourTwo. تمامی حقوق محفوظ است.
                    </div>
                    <div class="flex space-x-6 space-x-reverse">
                        <a href="#" class="text-sm text-gray-500 hover:text-black transition-colors">درباره ما</a>
                        <a href="#" class="text-sm text-gray-500 hover:text-black transition-colors">تماس</a>
                        <a href="#" class="text-sm text-gray-500 hover:text-black transition-colors">قوانین</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
