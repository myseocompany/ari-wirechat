import { useState } from 'react';
import { DollarSign } from 'lucide-react';

interface HourlyWageStepProps {
  value?: number;
  currency: string;
  onNext: (value: number) => void;
  onBack: () => void;
}

export default function HourlyWageStep({
  value,
  currency,
  onNext,
  onBack,
}: HourlyWageStepProps) {
  const [input, setInput] = useState(value?.toString() || '');
  const [error, setError] = useState('');

  const handleNext = () => {
    const num = parseFloat(input);
    if (!input || isNaN(num) || num <= 0) {
      setError('Por favor ingresa un monto válido');
      return;
    }
    setError('');
    onNext(num);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-3 mb-6">
        <div className="p-3 bg-blue-100 rounded-lg">
          <DollarSign className="w-6 h-6 text-blue-600" />
        </div>
        <h2 className="text-2xl font-bold text-gray-900">
          ¿Cuánto pagas por hora a un operario?
        </h2>
      </div>

      <div>
        <div className="relative">
          <span className="absolute left-4 top-3 text-gray-500 text-lg font-semibold">
            {currency}
          </span>
          <input
            type="number"
            step="0.01"
            value={input}
            onChange={(e) => setInput(e.target.value)}
            onKeyDown={(e) => e.key === 'Enter' && handleNext()}
            placeholder={value ? value.toString() : '0.00'}
            className="w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none text-lg"
          />
        </div>
        {error && <p className="text-red-500 text-sm mt-2">{error}</p>}
      </div>

      <div className="bg-blue-50 p-4 rounded-lg">
        <p className="text-sm text-blue-800">
          <strong>Tip:</strong> Prellenamos un valor de referencia según tu país. Ajusta
          si pagas diferente o quieres simular otro escenario.
        </p>
      </div>

      <div className="sticky bottom-0 z-20 -mx-8 mt-6 flex gap-3 border-t border-slate-200 bg-white/95 px-8 py-4 backdrop-blur">
        <button
          onClick={onBack}
          className="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition-all"
        >
          Atrás
        </button>
        <button
          onClick={handleNext}
          className="flex-1 px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-semibold transition-all shadow-lg"
        >
          Continuar
        </button>
      </div>
    </div>
  );
}
