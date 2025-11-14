<template>
  <div class="home-page">
    <div class="container">
      <el-card class="welcome-card">
        <h2 class="page-title">Добро пожаловать в WebGraphs</h2>
        <p class="page-description">
          Приложение для демонстрации финансовых графиков департамента
        </p>

        <el-row :gutter="20" class="info-cards">
          <el-col :span="12">
            <el-card class="tech-card">
              <template #header>
                <h3>Технологии</h3>
              </template>
              <ul>
                <li><el-icon><Check /></el-icon> Laravel 12</li>
                <li><el-icon><Check /></el-icon> Vue.js 3 (Composition API)</li>
                <li><el-icon><Check /></el-icon> Vue Router 4</li>
                <li><el-icon><Check /></el-icon> Vite</li>
                <li><el-icon><Check /></el-icon> Element Plus</li>
                <li><el-icon><Check /></el-icon> Less</li>
                <li><el-icon><Check /></el-icon> Docker Compose</li>
              </ul>
            </el-card>
          </el-col>

          <el-col :span="12">
            <el-card class="features-card">
              <template #header>
                <h3>Возможности</h3>
              </template>
              <ul>
                <li>• Интерактивные графики</li>
                <li>• Финансовая аналитика</li>
                <li>• Экспорт данных</li>
                <li>• Отчеты в реальном времени</li>
              </ul>
            </el-card>
          </el-col>
        </el-row>

        <div class="action-section">
          <el-button type="primary" size="large" @click="$router.push('/graphs')">
            Перейти к графикам
          </el-button>
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { Check } from '@element-plus/icons-vue';
</script>

<style lang="less" scoped>
.home-page {
  padding: 40px 20px;

  .container {
    max-width: 1200px;
    margin: 0 auto;

    .welcome-card {
      .page-title {
        font-size: 32px;
        font-weight: bold;
        color: #303133;
        margin-bottom: 16px;
      }

      .page-description {
        font-size: 16px;
        color: #606266;
        margin-bottom: 32px;
      }

      .info-cards {
        margin-bottom: 32px;

        .tech-card,
        .features-card {
          h3 {
            margin: 0;
            font-weight: 600;
          }

          ul {
            list-style: none;

            li {
              padding: 8px 0;
              display: flex;
              align-items: center;
              gap: 8px;
            }
          }
        }

        .tech-card {
          :deep(.el-card__header) {
            background-color: #f0f9ff;
          }

          ul li {
            color: #67c23a;
          }
        }

        .features-card {
          :deep(.el-card__header) {
            background-color: #ecf5ff;
          }

          ul li {
            color: #409eff;
          }
        }
      }

      .action-section {
        text-align: center;
      }
    }
  }
}
</style>
