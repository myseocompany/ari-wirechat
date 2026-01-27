import { useMemo } from 'react';
import { CAPABILITY_LABELS, MACHINE_MODELS, type MachineRegion } from '../../config/countries';
import { formatCurrency } from '../../utils/calculations';

interface MachineMediaReportProps {
  modelName?: string;
  priceRegion?: MachineRegion;
}

const CURRENCY_SYMBOL: Record<'COP' | 'USD', string> = {
  COP: '$',
  USD: '$',
};

const getDecimals = (currency: 'COP' | 'USD') => (currency === 'COP' ? 0 : 2);

export default function MachineMediaReport({ modelName, priceRegion }: MachineMediaReportProps) {
  const machine = useMemo(() => MACHINE_MODELS.find((m) => m.name === modelName), [modelName]);

  if (!machine) {
    return (
      <div className="bg-white border-2 border-gray-200 rounded-xl p-4 text-sm text-gray-600">
        Completa el paso de compatibilidad con máquina para ver el reporte detallado del modelo recomendado.
      </div>
    );
  }

  const region = priceRegion && machine.prices[priceRegion] ? priceRegion : 'CO';
  const currentPrice = machine.prices[region];
  const priceSymbol = CURRENCY_SYMBOL[currentPrice.currency];

  return (
    <div className="bg-white border-2 border-gray-200 rounded-2xl p-6 space-y-5">
      <div>
        <p className="text-xs font-semibold text-gray-500 uppercase tracking-wide">Modelo recomendado</p>
        <h3 className="text-2xl font-bold text-gray-900">{machine.name}</h3>
        <p className="text-sm text-gray-600">
          {machine.empanadasPerHour.toLocaleString()} empanadas/hora · Video y galería multimedia
        </p>
      </div>

      <div className="grid md:grid-cols-[2fr,3fr] gap-6">
        <div className="space-y-3">
          <div className="overflow-hidden rounded-xl">
            <img
              src={machine.media.photos[0]}
              alt={`Foto principal ${machine.name}`}
              className="w-full h-48 object-cover"
              loading="lazy"
            />
          </div>
          <div className="grid grid-cols-4 gap-2">
            {machine.media.photos.slice(1).map((photo) => (
              <div key={photo} className="overflow-hidden rounded-lg">
                <img src={photo} alt={`${machine.name}`} className="w-full h-16 object-cover" loading="lazy" />
              </div>
            ))}
          </div>
          <a
            href={machine.media.video}
            target="_blank"
            rel="noreferrer"
            className="inline-flex items-center justify-center gap-2 w-full px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition"
          >
            Ver video demostrativo
          </a>
        </div>

        <div className="space-y-4">
          <div className="bg-gray-50 rounded-xl p-4">
            <p className="text-sm font-semibold text-gray-700 mb-2">Precio estimado</p>
            <p className="text-3xl font-bold text-gray-900">
              {formatCurrency(currentPrice.amount, priceSymbol, getDecimals(currentPrice.currency))}{' '}
              <span className="text-lg text-gray-500">{currentPrice.currency}</span>
            </p>
            <p className="text-xs text-gray-500">
              Región: {region}. Consulta con ventas para opciones de pago.
            </p>
          </div>

          <div>
            <p className="text-sm font-semibold text-gray-700 mb-2">Capacidades del modelo</p>
            <div className="flex flex-wrap gap-2">
              {machine.capabilities.map((capability) => (
                <span
                  key={capability}
                  className="px-3 py-1 rounded-full text-xs border border-blue-200 text-blue-700 bg-blue-50"
                >
                  {CAPABILITY_LABELS[capability]}
                </span>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
