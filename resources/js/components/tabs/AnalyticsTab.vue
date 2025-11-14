<template>
  <div class="analytics-tab">
    <div v-if="!isAdmin" class="locked-message">
      <el-icon :size="64" color="#909399"><Lock /></el-icon>
      <p>Раздел доступен только администраторам</p>
    </div>

    <div v-else-if="loading" class="loading-container">
      <el-skeleton :rows="5" animated />
    </div>

    <div v-else class="charts-container">
      <!-- Бюджет vs Факт -->
      <el-card class="chart-card" shadow="hover">
        <template #header>
          <div class="card-header">
            <h3>Бюджет vs Факт</h3>
            <el-select
              v-model="budgetMonth"
              size="small"
              style="width: 140px"
              @change="loadBudgetData"
              placeholder="Выберите месяц"
            >
              <el-option
                v-for="month in getMonthOptions"
                :key="`${month.value.year}-${month.value.month}`"
                :label="month.label"
                :value="`${month.value.year}-${month.value.month}`"
              />
            </el-select>
          </div>
        </template>
        <v-chart class="chart" :option="budgetChartOption" autoresize />
      </el-card>

      <!-- ROI по проектам -->
      <el-card class="chart-card" shadow="hover">
        <template #header>
          <div class="card-header">
            <h3>ROI по проектам</h3>
          </div>
        </template>
        <v-chart class="chart" :option="roiChartOption" autoresize />
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { BarChart } from 'echarts/charts';
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
} from 'echarts/components';
import VChart from 'vue-echarts';
import axios from 'axios';
import { useAuth } from '../../composables/useAuth';
import { ElMessage } from 'element-plus';
import { Lock } from '@element-plus/icons-vue';

// Регистрация компонентов ECharts
use([
  CanvasRenderer,
  BarChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
]);

const { isAdmin } = useAuth();
const loading = ref(true);
const budgetMonth = ref(null); // Будет установлен после загрузки доступных месяцев
const availableMonths = ref([]); // Список доступных месяцев с сервера
const budgetData = ref({ categories: [], planned: [], actual: [] });
const roiData = ref({ projects: [], roi: [], investment: [], return: [], status: [] });

// Функция форматирования валюты
const formatCurrency = (value) => {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};

// Получение опций месяцев для селектора (загружаются с сервера)
const getMonthOptions = computed(() => availableMonths.value);

// Загрузка доступных месяцев с бюджетными данными
const loadAvailableMonths = async () => {
  if (!isAdmin.value) return;

  try {
    const response = await axios.get('/api/charts/available-budget-months');
    availableMonths.value = response.data;

    // Устанавливаем первый доступный месяц по умолчанию (самый свежий)
    if (availableMonths.value.length > 0) {
      const firstMonth = availableMonths.value[0].value;
      budgetMonth.value = `${firstMonth.year}-${firstMonth.month}`;
    }
  } catch (error) {
    console.error('Ошибка загрузки доступных месяцев:', error);
    ElMessage.error('Не удалось загрузить список месяцев');
  }
};

// Загрузка данных бюджета
const loadBudgetData = async () => {
  if (!isAdmin.value || !budgetMonth.value) return;

  try {
    const [year, month] = budgetMonth.value.split('-').map(Number);

    const budgetResponse = await axios.get('/api/charts/budget-vs-fact', {
      params: { year, month }
    });
    budgetData.value = budgetResponse.data;
  } catch (error) {
    console.error('Ошибка загрузки данных бюджета:', error);
    ElMessage.error('Не удалось загрузить данные бюджета');
  }
};

// Загрузка данных ROI
const loadRoiData = async () => {
  if (!isAdmin.value) return;

  try {
    const roiResponse = await axios.get('/api/charts/roi');
    roiData.value = roiResponse.data;
  } catch (error) {
    console.error('Ошибка загрузки данных ROI:', error);
    ElMessage.error('Не удалось загрузить данные ROI');
  }
};

// Загрузка всех данных
const loadData = async () => {
  if (!isAdmin.value) return;

  try {
    loading.value = true;

    // Сначала загружаем доступные месяцы
    await loadAvailableMonths();

    // Затем загружаем данные графиков
    await Promise.all([loadBudgetData(), loadRoiData()]);
  } finally {
    loading.value = false;
  }
};

// Конфигурация графика бюджет vs факт
const budgetChartOption = computed(() => ({
  tooltip: {
    trigger: 'axis',
    axisPointer: {
      type: 'shadow',
    },
    formatter: (params) => {
      let result = `${params[0].name}<br/>`;
      params.forEach((param) => {
        result += `${param.seriesName}: ${formatCurrency(param.value)}<br/>`;
      });
      return result;
    },
  },
  legend: {
    data: ['План', 'Факт'],
    bottom: 0,
  },
  xAxis: {
    type: 'category',
    data: budgetData.value.categories,
  },
  yAxis: {
    type: 'value',
    axisLabel: {
      formatter: (value) => formatCurrency(value),
    },
  },
  series: [
    {
      name: 'План',
      type: 'bar',
      data: budgetData.value.planned,
      itemStyle: {
        color: '#409eff',
      },
    },
    {
      name: 'Факт',
      type: 'bar',
      data: budgetData.value.actual,
      itemStyle: {
        color: '#e6a23c',
      },
    },
  ],
  grid: {
    left: '60px',
    right: '20px',
    top: '20px',
    bottom: '60px',
  },
}));

// Конфигурация графика ROI
const roiChartOption = computed(() => ({
  tooltip: {
    trigger: 'axis',
    axisPointer: {
      type: 'shadow',
    },
    formatter: (params) => {
      const index = params[0].dataIndex;
      return `
        ${params[0].name}<br/>
        ROI: ${params[0].value.toFixed(2)}%<br/>
        Инвестиции: ${formatCurrency(roiData.value.investment[index])}<br/>
        Возврат: ${formatCurrency(roiData.value.return[index])}<br/>
        Статус: ${roiData.value.status[index] === 'active' ? 'Активен' : 'Завершен'}
      `;
    },
  },
  xAxis: {
    type: 'value',
    axisLabel: {
      formatter: '{value}%',
    },
  },
  yAxis: {
    type: 'category',
    data: roiData.value.projects,
    axisLabel: {
      interval: 0,
      formatter: (value) => {
        return value.length > 20 ? value.substring(0, 20) + '...' : value;
      },
    },
  },
  series: [
    {
      type: 'bar',
      data: roiData.value.roi.map((value) => ({
        value,
        itemStyle: {
          color: value >= 0 ? '#67c23a' : '#f56c6c',
        },
      })),
      barWidth: '60%',
    },
  ],
  grid: {
    left: '200px',
    right: '40px',
    top: '20px',
    bottom: '40px',
  },
}));

// Экспортируем метод для загрузки данных извне
defineExpose({
  loadData,
});

// Загружаем данные при монтировании компонента (если админ)
onMounted(() => {
  if (isAdmin.value) {
    loadData();
  }
});
</script>

<style lang="less" scoped>
.analytics-tab {
  .loading-container {
    padding: 40px;
  }

  .locked-message {
    padding: 80px 20px;
    text-align: center;
    color: #909399;

    .el-icon {
      margin-bottom: 16px;
    }

    p {
      margin: 0;
      font-size: 16px;
      font-weight: 500;
    }
  }

  .charts-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 24px;

    .chart-card {
      position: relative;

      .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;

        h3 {
          margin: 0;
          font-size: 18px;
          font-weight: 600;
        }
      }

      .chart {
        height: 350px;
        width: 100%;
      }
    }
  }

  @media (max-width: 768px) {
    .charts-container {
      grid-template-columns: 1fr;

      .chart-card .chart {
        height: 300px;
      }
    }
  }
}
</style>
