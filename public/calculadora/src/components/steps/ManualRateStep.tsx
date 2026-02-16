import { useState } from 'react';
import { Users } from 'lucide-react';

interface ManualRateStepProps {
  value?: number;
  onNext: (value: number) => void;
  onBack: () => void;
}

export default function ManualRateStep({ value, onNext, onBack }: ManualRateStepProps) {
  const [input, setInput] = useState(value?.toString() || '');
  const [error, setError] = useState('');

  const handleNext = () => {
    const num = parseInt(input);
    if (!input || isNaN(num) || num <= 0) {
      setError('Por favor ingresa un número válido');
      return;
    }
    setError('');
    onNext(num);
  };

  const suggestedValues = [30, 50, 80, 100, 150];

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-3 mb-6">
        <div className="p-3 bg-blue-100 rounded-lg">
          <Users className="w-6 h-6 text-blue-600" />
        </div>
        <h2 className="text-2xl font-bold text-gray-900">
          ¿Cuántas empanadas produce un operario por hora manualmente?
        </h2>
      </div>

      <div>
        <input
          type="number"
          value={input}
          onChange={(e) => setInput(e.target.value)}
          onKeyDown={(e) => e.key === 'Enter' && handleNext()}
          placeholder="Ej: 80"
          className="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none text-lg"
        />
        {error && <p className="text-red-500 text-sm mt-2">{error}</p>}
      </div>

      <div className="space-y-2">
        <p className="text-sm text-gray-600">Valores comunes:</p>
        <div className="grid grid-cols-3 sm:grid-cols-5 gap-2">
          {suggestedValues.map((val) => (
            <button
              key={val}
              onClick={() => setInput(val.toString())}
              className="px-4 py-2 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all"
            >
              {val}
            </button>
          ))}
        </div>
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
