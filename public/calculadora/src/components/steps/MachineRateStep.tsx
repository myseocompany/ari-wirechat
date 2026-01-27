import { useState } from 'react';
import { Zap } from 'lucide-react';
import { MACHINE_MODELS, type ProductCapability } from '../../config/countries';

interface MachineRateStepProps {
  value?: number;
  onNext: (value: number) => void;
  onBack: () => void;
  selectedModel?: string;
  doughType?: 'trigo' | 'maiz' | 'maiz-trigo';
  productType?: 'empanadas' | 'empanadas-otros' | 'otros';
}

export default function MachineRateStep({
  value,
  onNext,
  onBack,
  selectedModel,
  doughType,
  productType,
}: MachineRateStepProps) {
  const [input, setInput] = useState(value?.toString() || '');
  const [error, setError] = useState('');

  const capabilityTags =
    productType === 'empanadas'
      ? doughType === 'maiz-trigo'
        ? ['empanadas-maiz', 'empanadas-trigo']
        : [`empanadas-${doughType || 'maiz'}`]
      : productType === 'empanadas-otros'
        ? [
            ...(doughType === 'maiz-trigo'
              ? ['empanadas-maiz', 'empanadas-trigo']
              : [`empanadas-${doughType || 'maiz'}`]),
            'arepas-rellenas',
            'patacones',
            'aborrajados',
            'pasteles',
            'arepas',
          ]
        : productType === 'otros'
          ? ['arepas-rellenas', 'patacones', 'aborrajados', 'pasteles']
          : [];

  const suggestedModels =
    capabilityTags.length > 0
      ? MACHINE_MODELS.filter((model) =>
          capabilityTags.some((tag) =>
            model.capabilities.includes(tag as ProductCapability),
          ),
        )
      : MACHINE_MODELS;

  const modelsToShow = suggestedModels.length ? suggestedModels : MACHINE_MODELS;

  const handleNext = () => {
    const num = parseInt(input);
    if (!input || isNaN(num) || num <= 0) {
      setError('Por favor ingresa un número válido');
      return;
    }
    setError('');
    onNext(num);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-3 mb-6">
        <div className="p-3 bg-green-100 rounded-lg">
          <Zap className="w-6 h-6 text-green-600" />
        </div>
        <h2 className="text-2xl font-bold text-gray-900">
          ¿Cuántas empanadas produciría con la máquina?
        </h2>
      </div>

      <div>
        <input
          type="number"
          value={input}
          onChange={(e) => setInput(e.target.value)}
          onKeyDown={(e) => e.key === 'Enter' && handleNext()}
          placeholder="Ej: 1200"
          className="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none text-lg"
        />
        {error && <p className="text-red-500 text-sm mt-2">{error}</p>}
      </div>

      <div className="space-y-3">
        <p className="text-sm text-gray-600 font-semibold">Modelos sugeridos:</p>
        {modelsToShow.map((model) => (
          <button
            key={model.name}
            onClick={() => setInput(model.empanadasPerHour.toString())}
            className="w-full p-4 border-2 border-gray-200 rounded-lg text-left hover:border-blue-500 hover:bg-blue-50 transition-all"
          >
            <div className="flex justify-between items-center">
              <div>
                <div className="font-semibold text-gray-900">
                  {model.name}
                  {selectedModel === model.name && (
                    <span className="ml-2 text-xs text-blue-600 font-semibold">
                      (Preseleccionado)
                    </span>
                  )}
                </div>
                <div className="text-sm text-gray-600">
                  {model.empanadasPerHour} emp/hora
                </div>
              </div>
              <div className="text-blue-600 font-semibold">Seleccionar</div>
            </div>
          </button>
        ))}
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
