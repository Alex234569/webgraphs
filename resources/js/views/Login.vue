<template>
  <div class="login-page">
    <el-card class="login-card">
      <template #header>
        <div class="login-header">
          <h2>Финансовая Аналитика</h2>
        </div>
      </template>

      <el-form
        ref="loginFormRef"
        :model="loginForm"
        :rules="rules"
        label-position="top"
        @submit.prevent="handleLogin"
      >
        <el-form-item label="Email" prop="email">
          <el-input
            v-model="loginForm.email"
            type="email"
            placeholder="Введите email"
            size="large"
            :prefix-icon="User"
          />
        </el-form-item>

        <el-form-item label="Пароль" prop="password">
          <el-input
            v-model="loginForm.password"
            type="password"
            placeholder="Введите пароль"
            size="large"
            :prefix-icon="Lock"
            show-password
            @keyup.enter="handleLogin"
          />
        </el-form-item>

        <el-form-item>
          <el-button
            type="primary"
            size="large"
            :loading="isLoading"
            @click="handleLogin"
            style="width: 100%"
          >
            Войти
          </el-button>
        </el-form-item>
      </el-form>

      <el-divider />

      <div class="test-accounts">
        <p class="hint-text">Тестовые учетные записи:</p>
        <div class="accounts-grid">
          <div class="account-item">
            <el-tag type="success">admin@app.me / admin</el-tag>
            <el-button
              size="small"
              type="success"
              :loading="isLoading"
              @click="quickLogin('admin@app.me', 'admin')"
            >
              Войти
            </el-button>
          </div>
          <div class="account-item">
            <el-tag type="info">user@app.me / user</el-tag>
            <el-button
              size="small"
              type="info"
              :loading="isLoading"
              @click="quickLogin('user@app.me', 'user')"
            >
              Войти
            </el-button>
          </div>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { User, Lock } from '@element-plus/icons-vue';
import { useAuth } from '../composables/useAuth';

const router = useRouter();
const { login, isLoading } = useAuth();

const loginFormRef = ref(null);
const loginForm = reactive({
  email: '',
  password: '',
});

const rules = {
  email: [
    { required: true, message: 'Введите email', trigger: 'blur' },
    { type: 'email', message: 'Введите корректный email', trigger: 'blur' },
  ],
  password: [
    { required: true, message: 'Введите пароль', trigger: 'blur' },
    { min: 3, message: 'Пароль должен быть не менее 3 символов', trigger: 'blur' },
  ],
};

const handleLogin = async () => {
  if (!loginFormRef.value) return;

  await loginFormRef.value.validate(async (valid) => {
    if (valid) {
      const result = await login(loginForm.email, loginForm.password);

      if (result.success) {
        ElMessage.success('Успешный вход!');
        router.push('/');
      } else {
        ElMessage.error(result.message || 'Ошибка входа');
      }
    }
  });
};

const quickLogin = async (email, password) => {
  const result = await login(email, password);

  if (result.success) {
    ElMessage.success('Успешный вход!');
    router.push('/');
  } else {
    ElMessage.error(result.message || 'Ошибка входа');
  }
};
</script>

<style lang="less" scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;

  .login-card {
    width: 100%;
    max-width: 420px;

    :deep(.el-card__header) {
      text-align: center;
      background: #f5f7fa;

      .login-header {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;

        h2 {
          margin: 0;
          font-size: 24px;
          color: #303133;
        }
      }
    }

    .test-accounts {
      .hint-text {
        font-size: 14px;
        color: #909399;
        margin-bottom: 12px;
        text-align: center;
      }

      .accounts-grid {
        display: flex;
        flex-direction: column;
        gap: 8px;

        .account-item {
          display: flex;
          align-items: center;
          justify-content: space-between;
          gap: 12px;

          .el-tag {
            flex: 1;
            justify-content: center;
            font-family: 'Courier New', monospace;
          }
        }
      }
    }
  }
}
</style>
