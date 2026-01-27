import { useState } from 'react';
import { Clock } from 'lucide-react';

interface DailyHoursStepProps {
  value?: number;
  onNext: (value: number) => void;
  onBack: () => void;
}

export default function DailyHoursStep({ value, onNext, onBack }: DailyHoursStepProps) {
  const [input, setInput] = useState(value?.toString() || '');
  const [error, setError] = useState('');

  const handleNext = () => {
    const num = parseInt(input);
    if (!input || isNaN(num) || num <= 0 || num > 24) {
      setError('Por favor ingresa un número válido (1-24 horas)');
      return;
    }
    setError('');
    onNext(num);
  };

  const suggestedValues = [8, 10, 12, 16, 24];

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-3 mb-6">
        <div className="p-3 bg-green-100 rounded-lg">
          <Clock className="w-6 h-6 text-green-600" />
        </div>
        <h2 className="text-2xl font-bold text-gray-900">
          ¿Cuántas horas trabajas al día?
        </h2>
      </div>

      <div>
        <input
          type="number"
          value={input}
          onChange={(e) => setInput(e.target.value)}
          onKeyDown={(e) => e.key === 'Enter' && handleNext()}
          placeholder="Ej: 8"
          className="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none text-lg"
        />
        {error && <p className="text-red-500 text-sm mt-2">{error}</p>}
      </div>

      <div className="space-y-2">
        <p className="text-sm text-gray-600">Jornadas comunes:</p>
        <div className="grid grid-cols-3 sm:grid-cols-5 gap-2">
          {suggestedValues.map((val) => (
            <button
              key={val}
              onClick={() => setInput(val.toString())}
              className="px-4 py-2 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all"
            >
              {val}h
            </button>
          ))}
        </div>
      </div>

      <div className="flex gap-3 pt-4">
        <button
          onClick={onBack}
          className="px-6 py-3 border-2 border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition-all"
        >
          Atrás
        </button>
        <button
          onClick={handleNext}
          className="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-green-500 text-white rounded-lg font-semibold hover:from-blue-600 hover:to-green-600 transition-all shadow-lg"
        >
          Continuar
        </button>
      </div>
    </div>
  );
}
