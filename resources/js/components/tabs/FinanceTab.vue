<template>
  <div class="finance-tab">
    <div class="filters-bar">
      <el-date-picker
        :model-value="dateRange"
        @update:model-value="updateDateRange"
        type="daterange"
        range-separator="—"
        start-placeholder="Начало"
        end-placeholder="Конец"
        format="DD.MM.YYYY"
        :shortcuts="dateShortcuts"
      />
    </div>

    <div v-if="loading" class="loading-container">
      <el-skeleton :rows="5" animated />
    </div>

    <div v-else class="charts-container">
      <!-- Доходы по месяцам -->
      <el-card class="chart-card" shadow="hover">
        <template #header>
          <div class="card-header">
            <h3>Доходы по месяцам</h3>
            <el-tag type="success" size="small">
              Всего: {{ formatCurrency(revenueData.total) }}
            </el-tag>
          </div>
        </template>
        <v-chart class="chart" :option="revenueChartOption" autoresize />
      </el-card>

      <!-- Расходы по категориям -->
      <el-card class="chart-card" shadow="hover">
        <template #header>
          <div class="card-header">
            <h3>Расходы по категориям</h3>
            <el-tag type="danger" size="small">
              Всего: {{ formatCurrency(expensesData.total) }}
            </el-tag>
          </div>
        </template>
        <v-chart class="chart" :option="expensesChartOption" autoresize />
      </el-card>

      <!-- Прибыль -->
      <el-card class="chart-card full-width" shadow="hover">
        <template #header>
          <h3>Прибыль за период</h3>
        </template>
        <v-chart class="chart" :option="profitChartOption" autoresize />
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { LineChart, BarChart, PieChart } from 'echarts/charts';
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
} from 'echarts/components';
import VChart from 'vue-echarts';
import axios from 'axios';
import { ElMessage } from 'element-plus';

// Регистрация компонентов ECharts
use([
  CanvasRenderer,
  LineChart,
  BarChart,
  PieChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
]);

const props = defineProps({
  months: {
    type: Number,
    required: true,
  },
  dateRange: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['update:dateRange']);

// Обработчик изменения диапазона дат
const updateDateRange = (value) => {
  emit('update:dateRange', value);
};

// Быстрые фильтры для дат
const dateShortcuts = [
  {
    text: '3 месяца',
    value: () => {
      const end = new Date();
      const start = new Date();
      start.setMonth(start.getMonth() - 3);
      return [start, end];
    },
  },
  {
    text: '6 месяцев',
    value: () => {
      const end = new Date();
      const start = new Date();
      start.setMonth(start.getMonth() - 6);
      return [start, end];
    },
  },
  {
    text: 'Год',
    value: () => {
      const end = new Date();
      const start = new Date();
      start.setMonth(start.getMonth() - 12);
      return [start, end];
    },
  },
];

const loading = ref(true);
const revenueData = ref({ labels: [], data: [], total: 0 });
const expensesData = ref({ categories: [], data: [], total: 0 });
const profitData = ref({ labels: [], data: [] });

// Функция форматирования валюты
const formatCurrency = (value) => {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};

// Загрузка данных
const loadData = async () => {
  try {
    loading.value = true;

    const [revenueResponse, expensesResponse, profitResponse] = await Promise.all([
      axios.get('/api/charts/revenue', { params: { months: props.months } }),
      axios.get('/api/charts/expenses', { params: { months: props.months } }),
      axios.get('/api/charts/profit', { params: { months: props.months } }),
    ]);

    revenueData.value = revenueResponse.data;
    expensesData.value = expensesResponse.data;
    profitData.value = profitResponse.data;
  } catch (error) {
    console.error('Ошибка загрузки финансовых данных:', error);
    ElMessage.error('Не удалось загрузить финансовые данные');
  } finally {
    loading.value = false;
  }
};

// Конфигурация графика доходов
const revenueChartOption = computed(() => ({
  tooltip: {
    trigger: 'axis',
    formatter: (params) => {
      const value = formatCurrency(params[0].value);
      return `${params[0].name}<br/>${value}`;
    },
  },
  xAxis: {
    type: 'category',
    data: revenueData.value.labels,
  },
  yAxis: {
    type: 'value',
    axisLabel: {
      formatter: (value) => formatCurrency(value),
    },
  },
  series: [
    {
      data: revenueData.value.data,
      type: 'line',
      smooth: true,
      areaStyle: {
        color: {
          type: 'linear',
          x: 0,
          y: 0,
          x2: 0,
          y2: 1,
          colorStops: [
            { offset: 0, color: 'rgba(103, 194, 58, 0.5)' },
            { offset: 1, color: 'rgba(103, 194, 58, 0.05)' },
          ],
        },
      },
      lineStyle: {
        color: '#67c23a',
        width: 3,
      },
      itemStyle: {
        color: '#67c23a',
      },
    },
  ],
  grid: {
    left: '60px',
    right: '20px',
    top: '20px',
    bottom: '40px',
  },
}));

// Конфигурация графика расходов
const expensesChartOption = computed(() => ({
  tooltip: {
    trigger: 'item',
    formatter: (params) => {
      const value = formatCurrency(params.value);
      return `${params.name}<br/>${value} (${params.percent}%)`;
    },
  },
  legend: {
    orient: 'horizontal',
    bottom: 0,
    type: 'scroll',
  },
  series: [
    {
      type: 'pie',
      radius: ['40%', '70%'],
      avoidLabelOverlap: false,
      itemStyle: {
        borderRadius: 10,
        borderColor: '#fff',
        borderWidth: 2,
      },
      label: {
        show: false,
        position: 'center',
      },
      emphasis: {
        label: {
          show: true,
          fontSize: 18,
          fontWeight: 'bold',
        },
      },
      labelLine: {
        show: false,
      },
      data: expensesData.value.categories.map((category, index) => ({
        value: expensesData.value.data[index],
        name: category,
      })),
    },
  ],
}));

// Конфигурация графика прибыли
const profitChartOption = computed(() => ({
  tooltip: {
    trigger: 'axis',
    axisPointer: {
      type: 'shadow',
    },
    formatter: (params) => {
      const value = formatCurrency(params[0].value);
      return `${params[0].name}<br/>${value}`;
    },
  },
  xAxis: {
    type: 'category',
    data: profitData.value.labels,
  },
  yAxis: {
    type: 'value',
    axisLabel: {
      formatter: (value) => formatCurrency(value),
    },
  },
  series: [
    {
      data: profitData.value.data.map((value) => ({
        value,
        itemStyle: {
          color: value >= 0 ? '#67c23a' : '#f56c6c',
        },
      })),
      type: 'bar',
      barWidth: '50%',
    },
  ],
  grid: {
    left: '60px',
    right: '20px',
    top: '20px',
    bottom: '40px',
  },
}));

// Загружаем данные при монтировании компонента
onMounted(() => {
  loadData();
});

// Перезагружаем данные при изменении периода
watch(() => props.months, () => {
  loadData();
});

// Экспортируем метод для загрузки данных извне
defineExpose({
  loadData,
});
</script>

<style lang="less" scoped>
.finance-tab {
  .filters-bar {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
    width: 320px;

    :deep(.el-date-editor) {
      width: 320px;
    }
  }
}

.loading-container {
  padding: 40px;
}

.charts-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
  gap: 24px;

  .chart-card {
    position: relative;

    &.full-width {
      grid-column: 1 / -1;
    }

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

      .el-tag {
        display: flex;
        align-items: center;
        gap: 4px;
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
</style>
