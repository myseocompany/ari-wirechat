import { useMemo, useState } from 'react';
import PhoneInput from 'react-phone-number-input';
import 'react-phone-number-input/style.css';
import { isValidPhoneNumber } from 'libphonenumber-js';
import { QuizData, calculateROI, formatCurrency, formatNumber } from '../../utils/calculations';
import { TrendingUp, Zap, AlertTriangle, Phone, AlertCircle } from 'lucide-react';
import { buildLeadPayload, sendQuizLead } from '../../services/api';

interface PartialResultsProps {
  quizData: Partial<QuizData>;
  onNext: (phone?: string) => void;
}

export default function PartialResults({ quizData, onNext }: PartialResultsProps) {
  const results = calculateROI(quizData as QuizData);
  const isCOP = quizData.currency === 'COP';
  const currencyDecimals = isCOP ? 0 : 2;
  const perUnitDecimals = isCOP ? 0 : 4;
  const [phone, setPhone] = useState<string | undefined>(quizData.phone || '');
  const [error, setError] = useState('');

  const defaultCountry = useMemo(() => {
    const map: Record<string, string> = {
      CO: 'CO',
      CL: 'CL',
      AMERICA: 'MX',
      USA: 'US',
      EUROPA: 'ES',
      OCEANIA: 'AU',
    };
    return map[quizData.priceRegion || ''] || undefined;
  }, [quizData.priceRegion]);

  const handleSubmit = () => {
    if (!phone || !isValidPhoneNumber(phone)) {
      setError('Ingresa un número válido con prefijo internacional');
      return;
    }
    setError('');
    onNext(phone);
    // Enviar al CRM inmediatamente con los datos completos
    const leadData = { ...quizData, phone } as Partial<QuizData>;
    const payload = buildLeadPayload(leadData);
    if (payload) {
      sendQuizLead(leadData).catch((err) => console.error('Error auto-enviando lead', err));
    }
  };

  if (!results.isPositiveROI) {
    return (
      <div className="space-y-6">
        <div className="flex items-center gap-3 mb-6">
          <div className="p-3 bg-orange-100 rounded-lg">
            <AlertTriangle className="w-6 h-6 text-orange-600" />
          </div>
          <h2 className="text-2xl font-bold text-gray-900">
            Análisis de Viabilidad
          </h2>
        </div>

        <div className="bg-orange-50 border-2 border-orange-200 rounded-xl p-6">
          <p className="text-orange-900 font-semibold mb-2">
            Atención: Con los datos actuales no se genera ahorro positivo.
          </p>
          <p className="text-orange-800">
            Esto puede deberse a que la máquina seleccionada no aumenta
            significativamente la productividad o el costo operativo es muy bajo.
            Considera revisar los datos o explorar otras opciones de maquinaria.
          </p>
        </div>

        <div className="bg-blue-50 p-4 rounded-lg">
          <p className="text-sm text-blue-800">
            ¿Quieres recibir una asesoría personalizada? Ingresa tu WhatsApp y
            te ayudaremos a encontrar la mejor solución para tu negocio.
          </p>
        </div>

        <button
          onClick={() => onNext()}
          className="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-green-500 text-white rounded-lg font-semibold hover:from-blue-600 hover:to-green-600 transition-all shadow-lg"
        >
          Continuar
        </button>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-3 mb-6">
        <div className="p-3 bg-green-100 rounded-lg">
          <TrendingUp className="w-6 h-6 text-green-600" />
        </div>
        <h2 className="text-2xl font-bold text-gray-900">
          Tu cálculo preliminar: ¿en cuánto tiempo se paga la máquina?
        </h2>
      </div>

      <div className="bg-white border-2 border-blue-200 rounded-xl p-6 mt-6 space-y-4">
        <div className="flex items-center gap-3">
          <div className="p-3 bg-blue-50 rounded-lg">
            <Phone className="w-6 h-6 text-blue-600" />
          </div>
          <div>
            <h3 className="text-lg font-bold text-gray-900">Último paso</h3>
            <p className="text-sm text-gray-600">
              Deja tu WhatsApp para enviarte en cuántos meses se paga la máquina.
            </p>
          </div>
        </div>

        <div>
          <label className="block text-sm font-semibold text-gray-700 mb-2">
            WhatsApp
          </label>
          <div className="relative">
            <Phone className="absolute left-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none" />
            <div className="pl-10">
              <PhoneInput
                international
                defaultCountry={defaultCountry as any}
                value={phone}
                onChange={setPhone}
                onKeyDown={(e) => e.key === 'Enter' && handleSubmit()}
                className="phone-input w-full"
              />
            </div>
          </div>
          {error && (
            <div className="flex items-start gap-2 text-sm text-red-600 bg-red-50 border border-red-200 p-3 rounded-lg mt-2">
              <AlertCircle className="w-4 h-4 mt-0.5" />
              <p>{error}</p>
            </div>
          )}
        </div>
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div className="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border-2 border-blue-200">
          <div className="flex items-center gap-2 mb-2">
            <Zap className="w-5 h-5 text-blue-600" />
            <span className="text-sm font-semibold text-blue-600">AHORRO POR EMPANADA</span>
          </div>
          <div className="text-3xl font-bold text-blue-900">
            {formatCurrency(results.savingsPerUnit, quizData.currencySymbol || '$', perUnitDecimals)}
          </div>
        </div>

        <div className="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border-2 border-green-200">
          <div className="flex items-center gap-2 mb-2">
            <TrendingUp className="w-5 h-5 text-green-600" />
            <span className="text-sm font-semibold text-green-600">INCREMENTO DE EFICIENCIA</span>
          </div>
          <div className="text-3xl font-bold text-green-900">
            +{formatNumber(results.efficiencyIncreasePercent, 1)}%
          </div>
        </div>

        <div className="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border-2 border-orange-200">
          <div className="flex items-center gap-2 mb-2">
            <Zap className="w-5 h-5 text-orange-600" />
            <span className="text-sm font-semibold text-orange-600">PRODUCCIÓN CON MÁQUINA</span>
          </div>
          <div className="text-3xl font-bold text-orange-900">
            {formatNumber(quizData.machineEmpanadasPerHour || 0)} <span className="text-lg">emp/h</span>
          </div>
        </div>
      </div>
      <div className="sticky bottom-0 z-20 -mx-8 mt-6 flex gap-3 border-t border-slate-200 bg-white/95 px-8 py-4 backdrop-blur">
        <button
          onClick={handleSubmit}
          className="flex-1 px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-semibold transition-all shadow-lg"
        >
          Ver mi cálculo completo
        </button>
      </div>
    </div>
  );
}
