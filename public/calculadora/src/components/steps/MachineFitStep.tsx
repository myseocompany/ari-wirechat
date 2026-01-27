import { useMemo, useState } from 'react';
import { AlertCircle, CheckCircle2 } from 'lucide-react';
import {
  CAPABILITY_LABELS,
  MACHINE_MODELS,
  type MachineModel,
  type ProductCapability,
  type MachineRegion,
} from '../../config/countries';

type DoughOption = 'maiz' | 'trigo' | 'maiz-trigo';
type ProductOption = 'empanadas' | 'empanadas-otros' | 'otros';

interface MachineFitStepProps {
  doughType?: DoughOption;
  productType?: ProductOption;
  machineModel?: string;
  priceRegion?: MachineRegion;
  onNext: (data: {
    doughType: DoughOption;
    productType: ProductOption;
    machineModel: string;
    machineEmpanadasPerHour: number;
    machineCost: number;
  }) => void;
  onBack: () => void;
}

const productTags: Record<ProductOption, ProductCapability[]> = {
  empanadas: ['empanadas-trigo', 'empanadas-maiz'],
  'empanadas-otros': [
    'empanadas-trigo',
    'empanadas-maiz',
    'arepas-rellenas',
    'patacones',
    'aborrajados',
    'pasteles',
    'arepas',
  ],
  otros: ['arepas-rellenas', 'patacones', 'aborrajados', 'pasteles'],
};

function matchesSelection(model: MachineModel, dough?: DoughOption, product?: ProductOption) {
  // Si el cliente requiere maíz + trigo, solo modelos que soporten ambas masas
  if (dough === 'maiz-trigo') {
    const supportsBoth =
      model.capabilities.includes('empanadas-maiz') &&
      model.capabilities.includes('empanadas-trigo');
    // En este caso priorizamos solo esa compatibilidad y mostramos todos los que la tengan
    return supportsBoth;
  }

  if (!product) return true;
  const tags = productTags[product] || [];
  const empTag = dough ? (`empanadas-${dough}` as ProductCapability) : undefined;
  const extraTags = tags.filter((t) => t !== 'empanadas-trigo' && t !== 'empanadas-maiz') as ProductCapability[];

  // Para cualquier selección con masa específica, exigimos compatibilidad con esa masa de empanada
  if (empTag && !model.capabilities.includes(empTag)) {
    return false;
  }

  if (product === 'empanadas') {
    return empTag ? model.capabilities.includes(empTag) : false;
  }

  if (product === 'empanadas-otros') {
    const hasEmp = empTag ? model.capabilities.includes(empTag) : false;
    const hasExtras = extraTags.some((tag) => model.capabilities.includes(tag));
    return hasEmp && hasExtras;
  }

  // product === 'otros'
  return extraTags.some((tag) => model.capabilities.includes(tag));
}

export default function MachineFitStep({
  doughType,
  productType,
  machineModel,
  priceRegion,
  onNext,
  onBack,
}: MachineFitStepProps) {
  const [dough, setDough] = useState<DoughOption | undefined>(doughType);
  const [product, setProduct] = useState<ProductOption | undefined>(productType);
  const [selectedModel, setSelectedModel] = useState<string | undefined>(machineModel);
  const [error, setError] = useState('');

  const readyForModels = Boolean(dough && product);

  const recommendedModels = useMemo(() => {
    if (!readyForModels) return [];
    const filtered = MACHINE_MODELS.filter((model) => matchesSelection(model, dough, product));
    return filtered.length ? filtered : MACHINE_MODELS;
  }, [dough, product, readyForModels]);

  const selectedCapabilities = useMemo(() => {
    if (!product) return new Set<ProductCapability>();

    const base = productTags[product] || [];
    if (product === 'empanadas') {
      if (dough === 'maiz-trigo') {
        return new Set<ProductCapability>(['empanadas-maiz', 'empanadas-trigo']);
      }
      const cap = `empanadas-${(dough || 'maiz') as DoughOption}` as ProductCapability;
      return new Set<ProductCapability>([cap]);
    }

    if (product === 'empanadas-otros') {
      if (dough === 'maiz-trigo') {
        return new Set<ProductCapability>(['empanadas-maiz', 'empanadas-trigo', ...base]);
      }
      const empCap = `empanadas-${(dough || 'maiz') as DoughOption}` as ProductCapability;
      const extra = base.filter(
        (c) => c !== 'empanadas-trigo' && c !== 'empanadas-maiz',
      ) as ProductCapability[];
      return new Set<ProductCapability>([empCap, ...extra]);
    }

    return new Set<ProductCapability>(base as ProductCapability[]);
  }, [product, dough]);

  const formatPrice = (price: number, region: MachineRegion | undefined) => {
    if (!region || region === 'CO') {
      return `$${price.toLocaleString('es-CO')}`;
    }
    return `$${price.toLocaleString('en-US')}`;
  };

  const handleContinue = () => {
    if (!dough || !product || !selectedModel) {
      setError('Selecciona una masa, un producto y un modelo recomendado.');
      return;
    }
    const chosen = MACHINE_MODELS.find((m) => m.name === selectedModel);
    if (!chosen) {
      setError('Selecciona un modelo válido.');
      return;
    }
    setError('');
    const selectedRegion = priceRegion as MachineRegion | undefined;
    const chosenPrice =
      selectedRegion && chosen.prices[selectedRegion]
        ? chosen.prices[selectedRegion]
        : chosen.prices.CO;

    onNext({
      doughType: dough,
      productType: product,
      machineModel: chosen.name,
      machineEmpanadasPerHour: chosen.empanadasPerHour,
      machineCost: chosenPrice.amount,
    });
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-3 mb-6">
        <div className="p-3 bg-emerald-100 rounded-lg">
          <CheckCircle2 className="w-6 h-6 text-emerald-600" />
        </div>
        <h2 className="text-2xl font-bold text-gray-900">
          ¿Qué masa y producto vas a trabajar?
        </h2>
      </div>

      <div className="grid sm:grid-cols-2 gap-4">
        <div className="space-y-3">
          <p className="text-sm font-semibold text-gray-700">Tipo de masa</p>
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
            {(['maiz', 'trigo', 'maiz-trigo'] as DoughOption[]).map((option) => (
              <button
                key={option}
                onClick={() => setDough(option)}
                className={`p-3 border-2 rounded-lg text-gray-800 font-semibold transition-all text-sm whitespace-normal ${
                  dough === option ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 bg-white'
                }`}
              >
                {option === 'trigo' ? 'Trigo' : option === 'maiz-trigo' ? 'Maíz y Trigo' : 'Maíz'}
              </button>
            ))}
          </div>
        </div>

        <div className="space-y-3">
          <p className="text-sm font-semibold text-gray-700">Producto principal</p>
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
            {(['empanadas', 'empanadas-otros', 'otros'] as ProductOption[]).map((option) => (
              <button
                key={option}
                onClick={() => setProduct(option)}
                className={`p-3 border-2 rounded-lg text-gray-800 font-semibold transition-all text-sm whitespace-normal ${
                  product === option ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white'
                }`}
              >
                {option === 'empanadas' && 'Empanadas'}
                {option === 'empanadas-otros' && 'Empanadas + Otros'}
                {option === 'otros' && 'Otros'}
              </button>
            ))}
          </div>
        </div>
      </div>

      <div className="space-y-3">
        <p className="text-sm text-gray-600">
          Te mostramos los modelos recomendados según la masa y el producto que seleccionaste.
        </p>
        {!readyForModels && (
          <div className="p-4 border-2 border-dashed border-gray-200 rounded-xl text-sm text-gray-600 bg-gray-50">
            Selecciona primero la masa y el producto para ver las opciones de máquina.
          </div>
        )}
        {readyForModels && (
          <div className="space-y-3">
            {recommendedModels.map((model) => {
              const region = priceRegion as MachineRegion | undefined;
              const price =
                region && model.prices[region]
                  ? model.prices[region].amount
                  : model.prices.CO.amount;
              const currency =
                region && model.prices[region]
                  ? model.prices[region].currency
                  : model.prices.CO.currency;

              return (
                <button
                  key={model.name}
                  onClick={() => setSelectedModel(model.name)}
                  className={`w-full p-4 border-2 rounded-xl text-left transition-all hover:border-blue-500 hover:bg-blue-50 ${
                    selectedModel === model.name ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white'
                  }`}
                >
                  <div className="flex justify-between items-center">
                    <div>
                      <div className="font-semibold text-gray-900">{model.name}</div>
                      <div className="text-sm text-gray-600">
                        {model.empanadasPerHour} unidades/hora (referencia)
                      </div>
                      <div className="text-sm text-gray-500">
                        {currency} {formatPrice(price, region)}
                      </div>
                      <div className="flex flex-wrap gap-1 mt-2">
                        {model.capabilities.map((cap) => (
                          <span
                            key={cap}
                            className={`text-[11px] px-2 py-1 rounded-full border ${
                              selectedCapabilities.has(cap)
                                ? 'bg-blue-100 text-blue-700 border-blue-200'
                                : 'bg-gray-100 text-gray-700 border-gray-200'
                            }`}
                          >
                            {CAPABILITY_LABELS[cap]}
                          </span>
                        ))}
                      </div>
                    </div>
                    {selectedModel === model.name && (
                      <CheckCircle2 className="w-5 h-5 text-blue-600" />
                    )}
                  </div>
                </button>
              );
            })}
          </div>
        )}
      </div>

      {error && (
        <div className="flex items-start gap-2 text-sm text-red-600 bg-red-50 border border-red-200 p-3 rounded-lg">
          <AlertCircle className="w-4 h-4 mt-0.5" />
          <p>{error}</p>
        </div>
      )}

      <div className="flex gap-3 pt-4">
        <button
          onClick={onBack}
          className="px-6 py-3 border-2 border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition-all"
        >
          Atrás
        </button>
        <button
          onClick={handleContinue}
          className="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-green-500 text-white rounded-lg font-semibold hover:from-blue-600 hover:to-green-600 transition-all shadow-lg"
        >
          Continuar
        </button>
      </div>
    </div>
  );
}
