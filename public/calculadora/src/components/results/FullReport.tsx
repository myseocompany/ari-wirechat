import {
  QuizData,
  calculateROI,
  formatCurrency,
  formatNumber,
  getBusinessProfile,
} from '../../utils/calculations';
import {
  TrendingUp,
  Calendar,
  DollarSign,
  Zap,
  BarChart3,
  Scale,
  CheckCircle2,
  Download,
  RefreshCw,
} from 'lucide-react';
import PaybackChart from './PaybackChart';
import MachineMediaReport from './MachineMediaReport';
import { useMemo } from 'react';
import { useState } from 'react';
import { sendQuizLead } from '../../services/api';

interface FullReportProps {
  quizData: Partial<QuizData>;
  onRestart: () => void;
}

export default function FullReport({ quizData, onRestart }: FullReportProps) {
  const [sending, setSending] = useState(false);
  const [sent, setSent] = useState(false);
  const results = calculateROI(quizData as QuizData);
  const profile = getBusinessProfile(results.paybackMonths);
  const currency = quizData.currencySymbol || '$';
  const dailyProduction = quizData.manualDailyProduction || 0;
  const isCOP = quizData.currency === 'COP';
  const currencyDecimals = isCOP ? 0 : 2;
  const perUnitDecimals = isCOP ? 0 : 4;

  const comparisonTable = useMemo(() => {
    const manualHours =
      quizData.manualEmpanadasPerHour && dailyProduction
        ? dailyProduction / quizData.manualEmpanadasPerHour
        : 0;
    const machineHours =
      quizData.machineEmpanadasPerHour && dailyProduction
        ? dailyProduction / quizData.machineEmpanadasPerHour
        : 0;
    const hourly = quizData.hourlyWage || 0;
    return {
      manual: {
        hourlyRate: hourly,
        hours: manualHours,
        cost: hourly * manualHours,
      },
      machine: {
        hourlyRate: hourly, // mismo salario por operario de máquina
        hours: machineHours,
        cost: hourly * machineHours,
      },
      savings: hourly * (manualHours - machineHours),
    };
  }, [quizData.hourlyWage, quizData.manualEmpanadasPerHour, quizData.machineEmpanadasPerHour, dailyProduction]);

  const handleExportPDF = () => {
    window.print();
  };

  return (
    <div className="space-y-6">
      <div className="text-center mb-8">
        <div className="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
          <CheckCircle2 className="w-8 h-8 text-green-600" />
        </div>
        <h2 className="text-3xl font-bold text-gray-900 mb-2">
          Tu cálculo completo: ¿cuándo se paga la máquina?
        </h2>
        <p className="text-gray-600">
          Análisis detallado del tiempo para recuperar la inversión y el ahorro
        </p>
      </div>

      <div className="space-y-3">
        <div className="flex items-center gap-2">
          <span className="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-50 text-blue-600 font-semibold">
            1
          </span>
          <div>
            <p className="text-sm font-semibold text-blue-700 tracking-wide uppercase">
              Reporte de la máquina
            </p>
            <p className="text-gray-600 text-sm">
              Ficha visual, video y lista de precios del modelo recomendado
            </p>
          </div>
        </div>
        <MachineMediaReport modelName={quizData.machineModel} priceRegion={quizData.priceRegion} />
      </div>

      <div className="bg-gradient-to-br from-green-500 to-blue-500 text-white rounded-2xl p-8 shadow-xl">
        <div className="flex items-center gap-3 mb-4">
          <Calendar className="w-6 h-6" />
          <span className="text-sm font-semibold uppercase tracking-wide">
            Tiempo de Recuperación
          </span>
        </div>
        <div className="text-5xl font-bold mb-2">
          {formatNumber(results.paybackMonths, 1)} meses
        </div>
        <p className="text-green-100">
          Recuperarás tu inversión en menos de{' '}
          {Math.ceil(results.paybackMonths)} meses
        </p>
      </div>

      <PaybackChart
        monthlySavings={results.monthlySavings}
        machineCost={quizData.machineCost || 0}
        currencySymbol={currency}
      />

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div className="bg-white border-2 border-green-200 rounded-xl p-6">
          <div className="flex items-center gap-2 mb-3">
            <DollarSign className="w-5 h-5 text-green-600" />
            <span className="text-sm font-semibold text-green-600">AHORRO MENSUAL</span>
          </div>
          <div className="text-3xl font-bold text-gray-900">
            {formatCurrency(results.monthlySavings, currency, currencyDecimals)}
          </div>
          <p className="text-sm text-gray-600 mt-1">
            Ahorro promedio por mes de operación
          </p>
        </div>

        <div className="bg-white border-2 border-blue-200 rounded-xl p-6">
          <div className="flex items-center gap-2 mb-3">
            <TrendingUp className="w-5 h-5 text-blue-600" />
            <span className="text-sm font-semibold text-blue-600">INGRESO ADICIONAL AL AÑO</span>
          </div>
          <div className="text-3xl font-bold text-gray-900">
            {formatCurrency(results.annualSavings, currency, currencyDecimals)}
          </div>
          <p className="text-sm text-gray-600 mt-1">Ahorro anual proyectado</p>
        </div>
      </div>

      <div className="bg-gray-50 rounded-xl p-6">
        <div className="flex items-center gap-2 mb-4">
          <BarChart3 className="w-5 h-5 text-gray-700" />
          <h3 className="text-lg font-bold text-gray-900">
            Comparación de Costos por Empanada
          </h3>
        </div>
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div className="flex items-center gap-2">
            <span className="text-sm text-gray-600">Manual</span>
            <span className="text-2xl font-bold text-red-600">
              {formatCurrency(results.manualCostPerUnit, currency, perUnitDecimals)}
            </span>
          </div>
          <div className="flex items-center gap-2">
            <span className="text-sm text-gray-600">Con Máquina</span>
            <span className="text-2xl font-bold text-green-600">
              {formatCurrency(results.machineCostPerUnit, currency, perUnitDecimals)}
            </span>
          </div>
          <div className="flex items-center gap-2">
            <span className="text-sm text-gray-600">Ahorro</span>
            <span className="text-2xl font-bold text-blue-600">
              {formatCurrency(results.savingsPerUnit, currency, perUnitDecimals)}
            </span>
          </div>
        </div>
      </div>

      <div className="bg-white border-2 border-gray-200 rounded-xl p-6">
        <div className="flex items-center gap-2 mb-4">
          <Scale className="w-5 h-5 text-gray-700" />
          <h3 className="text-lg font-bold text-gray-900">
            Comparativo Diario (meta actual)
          </h3>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <p className="text-sm text-gray-600">Costo con persona</p>
            <p className="text-2xl font-bold text-gray-900">
              {formatCurrency(
                results.manualCostPerUnit * (quizData.manualDailyProduction || 0),
                currency,
                currencyDecimals,
              )}
            </p>
          </div>
          <div>
            <p className="text-sm text-gray-600">Costo con máquina</p>
            <p className="text-2xl font-bold text-gray-900">
              {formatCurrency(
                results.machineCostPerUnit * (quizData.manualDailyProduction || 0),
                currency,
                currencyDecimals,
              )}
            </p>
          </div>
          <div>
            <p className="text-sm text-gray-600">Ahorro diario</p>
            <p className="text-2xl font-bold text-green-700">
              {formatCurrency(results.dailySavings, currency, currencyDecimals)}
            </p>
          </div>
        </div>
      </div>

      <div className="bg-white border-2 border-gray-200 rounded-xl p-6">
        <div className="flex items-center gap-2 mb-4">
          <Zap className="w-5 h-5 text-orange-600" />
          <h3 className="text-lg font-bold text-gray-900">
            Incremento de Productividad
          </h3>
        </div>
        <div className="grid grid-cols-2 gap-4 mb-4">
          <div>
            <p className="text-sm text-gray-600 mb-1">Manual</p>
            <p className="text-2xl font-bold text-gray-900">
              {formatNumber(quizData.manualEmpanadasPerHour || 0)} emp/h
            </p>
          </div>
          <div>
            <p className="text-sm text-gray-600 mb-1">Con Máquina</p>
            <p className="text-2xl font-bold text-orange-600">
              {formatNumber(quizData.machineEmpanadasPerHour || 0)} emp/h
            </p>
          </div>
        </div>
        <div className="bg-orange-50 p-4 rounded-lg">
          <p className="text-orange-900 font-semibold">
            Incremento de eficiencia:{' '}
            <span className="text-2xl">
              +{formatNumber(results.efficiencyIncreasePercent, 1)}%
            </span>
          </p>
        </div>
      </div>

      <div className="bg-white border-2 border-gray-200 rounded-xl p-6">
        <div className="flex items-center justify-between mb-4">
          <div className="flex items-center gap-2">
            <Scale className="w-5 h-5 text-gray-700" />
            <h3 className="text-lg font-bold text-gray-900">Tabla comparativa</h3>
          </div>
          <div className="text-sm text-gray-600">
            Meta diaria: {formatNumber(dailyProduction)} empanadas
          </div>
        </div>
        <div className="overflow-hidden rounded-lg border border-gray-200">
          <div className="grid grid-cols-3 bg-gray-50 text-sm font-semibold text-gray-700">
            <div className="px-4 py-3 border-r border-gray-200">Ítem</div>
            <div className="px-4 py-3 border-r border-gray-200 text-center">Manual</div>
            <div className="px-4 py-3 text-center">Con máquina</div>
          </div>
          <div className="grid grid-cols-3 text-sm text-gray-800 divide-y divide-gray-200">
            <div className="grid grid-cols-3 col-span-3">
              <div className="px-4 py-3 border-r border-gray-200">Empanadas por hora</div>
              <div className="px-4 py-3 text-center border-r border-gray-200">
                {formatNumber(quizData.manualEmpanadasPerHour || 0)}
              </div>
              <div className="px-4 py-3 text-center">
                {formatNumber(quizData.machineEmpanadasPerHour || 0)}
              </div>
            </div>
            <div className="grid grid-cols-3 col-span-3">
              <div className="px-4 py-3 border-r border-gray-200">Horas trabajadas</div>
              <div className="px-4 py-3 text-center border-r border-gray-200">
                {formatNumber(comparisonTable.manual.hours, 1)}
              </div>
              <div className="px-4 py-3 text-center">
                {formatNumber(comparisonTable.machine.hours, 1)}
              </div>
            </div>
            <div className="grid grid-cols-3 col-span-3">
              <div className="px-4 py-3 border-r border-gray-200">Valor hora operario</div>
              <div className="px-4 py-3 text-center border-r border-gray-200">
                {formatCurrency(comparisonTable.manual.hourlyRate, currency, currencyDecimals)}
              </div>
              <div className="px-4 py-3 text-center">
                {formatCurrency(comparisonTable.machine.hourlyRate, currency, currencyDecimals)}
              </div>
            </div>
            <div className="grid grid-cols-3 col-span-3">
              <div className="px-4 py-3 border-r border-gray-200">Costo por empanada</div>
              <div className="px-4 py-3 text-center border-r border-gray-200">
                {formatCurrency(results.manualCostPerUnit, currency, perUnitDecimals)}
              </div>
              <div className="px-4 py-3 text-center">
                {formatCurrency(results.machineCostPerUnit, currency, perUnitDecimals)}
              </div>
            </div>
            <div className="grid grid-cols-3 col-span-3">
              <div className="px-4 py-3 border-r border-gray-200">Costo mano de obra</div>
              <div className="px-4 py-3 text-center border-r border-gray-200">
                {formatCurrency(comparisonTable.manual.cost, currency, currencyDecimals)}
              </div>
              <div className="px-4 py-3 text-center">
                {formatCurrency(comparisonTable.machine.cost, currency, currencyDecimals)}
              </div>
            </div>
          </div>
        </div>
        <div className="mt-3 text-sm text-green-700 font-semibold">
          Ahorro en mano de obra: {formatCurrency(comparisonTable.savings, currency, currencyDecimals)}
        </div>
      </div>

      <div className="bg-blue-50 border-2 border-blue-200 rounded-xl p-6">
        <h3 className="text-lg font-bold text-blue-900 mb-3">
          Recomendación para tu perfil: {profile}
        </h3>
        {profile === 'Startup' && (
          <p className="text-blue-800">
            Excelente inversión. Tu tiempo de recuperación es muy corto, lo que
            indica que la máquina te permitirá escalar rápidamente. Considera
            reinvertir los ahorros en expandir tu producción o diversificar tu
            oferta.
          </p>
        )}
        {profile === 'Growth' && (
          <p className="text-blue-800">
            Buena inversión. Con un año de recuperación, esta máquina te ayudará a
            optimizar costos y aumentar tu capacidad productiva de manera sostenible.
            Planifica bien tu flujo de caja para los primeros meses.
          </p>
        )}
        {profile === 'Expanding' && (
          <p className="text-blue-800">
            Inversión de largo plazo. El tiempo de recuperación sugiere que debes
            asegurar un flujo constante de producción. Considera opciones de
            financiamiento para no comprometer tu capital de trabajo.
          </p>
        )}
      </div>

      <div className="flex flex-col sm:flex-row gap-3 pt-4">
        <button
          onClick={handleExportPDF}
          className="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gray-900 text-white rounded-lg font-semibold hover:bg-gray-800 transition-all"
        >
          <Download className="w-5 h-5" />
          Descargar PDF
        </button>
        <button
          onClick={onRestart}
          className="flex-1 flex items-center justify-center gap-2 px-6 py-3 border-2 border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition-all"
        >
          <RefreshCw className="w-5 h-5" />
          Nueva Simulación
        </button>
      </div>

      <div className="text-center pt-4">
        <p className="text-sm text-gray-600">
          ¿Necesitas asesoría personalizada? Contáctanos al WhatsApp que
          proporcionaste
        </p>
      </div>
    </div>
  );
}
