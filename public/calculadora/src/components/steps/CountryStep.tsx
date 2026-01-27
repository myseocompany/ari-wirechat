import { COUNTRIES, type CountryConfig } from '../../config/countries';
import { Globe } from 'lucide-react';

interface CountryStepProps {
  value?: string;
  onSelect: (country: CountryConfig) => void;
}

export default function CountryStep({ value, onSelect }: CountryStepProps) {
  return (
    <div className="space-y-6">
      <div className="flex items-center gap-3 mb-6">
        <div className="p-3 bg-blue-100 rounded-lg">
          <Globe className="w-6 h-6 text-blue-600" />
        </div>
        <h2 className="text-2xl font-bold text-gray-900">¿En qué país estás?</h2>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {COUNTRIES.map((country) => (
          <button
            key={country.code}
            onClick={() => onSelect(country)}
            className={`p-4 border-2 rounded-xl text-left transition-all hover:border-blue-500 hover:bg-blue-50 ${
              value === country.name
                ? 'border-blue-500 bg-blue-50'
                : 'border-gray-200 bg-white'
            }`}
          >
            <div className="font-semibold text-gray-900">{country.name}</div>
            <div className="text-sm text-gray-500">{country.currency}</div>
          </button>
        ))}
      </div>
    </div>
  );
}
