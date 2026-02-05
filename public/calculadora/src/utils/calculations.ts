import { DAYS_PER_MONTH, type MachineRegion } from '../config/countries';

export interface QuizData {
  country: string;
  currency: string;
  currencySymbol: string;
  priceRegion?: MachineRegion;
  doughType?: 'trigo' | 'maiz' | 'maiz-trigo';
  productType?: 'empanadas' | 'empanadas-otros' | 'otros';
  machineModel?: string;
  phone?: string;
  phonePrefix?: string;
  manualDailyProduction: number;
  manualEmpanadasPerHour: number;
  machineEmpanadasPerHour: number;
  hourlyWage: number;
  dailyHours: number;
  machineCost: number;
}

export interface CalculationResults {
  manualCostPerUnit: number;
  machineCostPerUnit: number;
  savingsPerUnit: number;
  dailySavings: number;
  monthlySavings: number;
  annualSavings: number;
  paybackMonths: number;
  efficiencyIncreasePercent: number;
  isPositiveROI: boolean;
}

export function calculateROI(data: QuizData): CalculationResults {
  const manualCostPerUnit = data.hourlyWage / data.manualEmpanadasPerHour;
  const machineCostPerUnit = data.hourlyWage / data.machineEmpanadasPerHour;
  const savingsPerUnit = manualCostPerUnit - machineCostPerUnit;
  const dailySavings = savingsPerUnit * data.manualDailyProduction;
  const monthlySavings = dailySavings * DAYS_PER_MONTH;
  const annualSavings = monthlySavings * 12;
  const paybackMonths = monthlySavings > 0 ? data.machineCost / monthlySavings : 0;
  const efficiencyIncreasePercent =
    ((data.machineEmpanadasPerHour - data.manualEmpanadasPerHour) /
      data.manualEmpanadasPerHour) *
    100;

  return {
    manualCostPerUnit,
    machineCostPerUnit,
    savingsPerUnit,
    dailySavings,
    monthlySavings,
    annualSavings,
    paybackMonths,
    efficiencyIncreasePercent,
    isPositiveROI: monthlySavings > 0,
  };
}

function formatWithThousands(value: number, decimals: number): string {
  const [integerPart, decimalPart] = value.toFixed(decimals).split('.');
  const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  return decimalPart === undefined ? formattedInteger : `${formattedInteger}.${decimalPart}`;
}

export function formatCurrency(amount: number, symbol: string, decimals = 2): string {
  return `${symbol}${formatWithThousands(amount, decimals)}`;
}

export function formatNumber(num: number, decimals = 0): string {
  return formatWithThousands(num, decimals);
}

export function getBusinessProfile(paybackMonths: number): string {
  if (paybackMonths <= 6) return 'Startup';
  if (paybackMonths <= 12) return 'Growth';
  return 'Expanding';
}
