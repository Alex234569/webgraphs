import { ref } from 'vue';
import axios from 'axios';

// Глобальное состояние пользователя
const user = ref(null);
const isLoading = ref(false);

// Настройка axios для работы с сессиями Laravel
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

export function useAuth() {
  /**
   * Вход пользователя
   */
  const login = async (email, password) => {
    isLoading.value = true;
    try {
      // Получаем CSRF cookie
      await axios.get('/sanctum/csrf-cookie');

      // Выполняем вход
      const response = await axios.post('/api/login', { email, password });
      user.value = response.data.user;

      return { success: true };
    } catch (error) {
      const message = error.response?.data?.message || 'Ошибка входа';
      return { success: false, message };
    } finally {
      isLoading.value = false;
    }
  };

  /**
   * Выход пользователя
   */
  const logout = async () => {
    isLoading.value = true;
    try {
      await axios.post('/api/logout');
      user.value = null;
      return { success: true };
    } catch (error) {
      console.error('Logout error:', error);
      return { success: false };
    } finally {
      isLoading.value = false;
    }
  };

  /**
   * Получить текущего пользователя
   */
  const fetchUser = async () => {
    isLoading.value = true;
    try {
      const response = await axios.get('/api/me');
      user.value = response.data.user;
      return { success: true };
    } catch (error) {
      user.value = null;
      return { success: false };
    } finally {
      isLoading.value = false;
    }
  };

  /**
   * Проверка, является ли пользователь админом
   */
  const isAdmin = () => {
    return user.value?.role === 'admin';
  };

  /**
   * Проверка авторизации
   */
  const isAuthenticated = () => {
    return user.value !== null;
  };

  return {
    user,
    isLoading,
    login,
    logout,
    fetchUser,
    isAdmin,
    isAuthenticated,
  };
}
