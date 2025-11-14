<template>
  <div id="app">
    <el-container>
      <!-- Хедер только для авторизованных пользователей -->
      <el-header v-if="isAuthenticated" class="app-header">
        <div class="header-content">
          <div class="header-title">
            <el-icon :size="24" color="#409eff"><TrendCharts /></el-icon>
            <h1 class="title">Финансовая Аналитика</h1>
          </div>

          <div class="user-section">
            <el-dropdown @command="handleCommand">
              <span class="user-dropdown">
                <el-avatar :size="32" :icon="UserFilled" />
                <span class="user-name">{{ user?.name }}</span>
                <el-tag v-if="isAdmin" type="warning" size="small">Admin</el-tag>
              </span>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item disabled>
                    <div class="user-info">
                      <div>{{ user?.email }}</div>
                      <small>{{ user?.role }}</small>
                    </div>
                  </el-dropdown-item>
                  <el-dropdown-item divided command="logout">
                    <el-icon><SwitchButton /></el-icon>
                    Выход
                  </el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>
        </div>
      </el-header>

      <el-main>
        <router-view />
      </el-main>
    </el-container>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { UserFilled, SwitchButton, TrendCharts } from '@element-plus/icons-vue';
import { useAuth } from './composables/useAuth';

const router = useRouter();
const { user, isAuthenticated, isAdmin, logout } = useAuth();

const handleCommand = async (command) => {
  if (command === 'logout') {
    const result = await logout();
    if (result.success) {
      ElMessage.success('Вы вышли из системы');
      router.push('/login');
    }
  }
};
</script>

<style lang="less" scoped>
.app-header {
  background: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  padding: 0;

  .header-content {
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
    padding: 0 20px;

    .header-title {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-shrink: 0;

      .title {
        font-size: 20px;
        font-weight: 600;
        color: #303133;
        margin: 0;
        letter-spacing: -0.5px;
      }
    }

    .user-section {
      flex-shrink: 0;

      .user-dropdown {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 4px;
        transition: background-color 0.3s;

        &:hover {
          background-color: #f5f7fa;
        }

        .user-name {
          font-weight: 500;
          color: #303133;
        }
      }

      .user-info {
        padding: 4px 0;

        div {
          color: #303133;
          font-weight: 500;
        }

        small {
          color: #909399;
          text-transform: uppercase;
        }
      }
    }
  }
}

:deep(.el-main) {
  padding: 0;
}

@media (max-width: 768px) {
  .app-header .header-content {
    .header-title .title {
      font-size: 16px;
    }

    .user-section .user-dropdown .user-name {
      display: none;
    }
  }
}
</style>
