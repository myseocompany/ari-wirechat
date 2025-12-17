export type MachineRegion = 'CO' | 'CL' | 'AMERICA' | 'USA' | 'EUROPA' | 'OCEANIA';

export interface CountryConfig {
  code: MachineRegion;
  name: string;
  currency: string;
  currencySymbol: string;
  suggestedHourlyWage: number;
  priceRegion: MachineRegion;
  phonePrefix: string;
}

type RegionPrices = {
  CO: { currency: 'COP'; amount: number };
  CL: { currency: 'USD'; amount: number };
  AMERICA: { currency: 'USD'; amount: number };
  USA: { currency: 'USD'; amount: number };
  EUROPA: { currency: 'USD'; amount: number };
  OCEANIA: { currency: 'USD'; amount: number };
};

export type ProductCapability =
  | 'empanadas-trigo'
  | 'empanadas-maiz'
  | 'arepas'
  | 'arepas-rellenas'
  | 'patacones'
  | 'aborrajados'
  | 'pasteles';

export interface MachineModel {
  name: string;
  empanadasPerHour: number;
  prices: RegionPrices;
  capabilities: ProductCapability[];
}

export interface MachinePrice {
  model: string;
  prices: RegionPrices;
}

export const COUNTRIES: CountryConfig[] = [
  {
    code: 'CO',
    name: 'Colombia',
    currency: 'COP',
    currencySymbol: '$',
    suggestedHourlyWage: 10895,
    priceRegion: 'CO',
    phonePrefix: '+57',
  },
  {
    code: 'CL',
    name: 'Chile',
    currency: 'USD',
    currencySymbol: '$',
    suggestedHourlyWage: 3.1,
    priceRegion: 'CL',
    phonePrefix: '+56',
  },
  {
    code: 'AMERICA',
    name: 'América',
    currency: 'USD',
    currencySymbol: '$',
    suggestedHourlyWage: 2.5,
    priceRegion: 'AMERICA',
    phonePrefix: '+52',
  },
  {
    code: 'USA',
    name: 'Estados Unidos',
    currency: 'USD',
    currencySymbol: '$',
    suggestedHourlyWage: 15,
    priceRegion: 'USA',
    phonePrefix: '+1',
  },
  {
    code: 'EUROPA',
    name: 'Europa',
    currency: 'USD',
    currencySymbol: '$',
    suggestedHourlyWage: 10,
    priceRegion: 'EUROPA',
    phonePrefix: '+34',
  },
  {
    code: 'OCEANIA',
    name: 'Oceanía',
    currency: 'USD',
    currencySymbol: '$',
    suggestedHourlyWage: 16,
    priceRegion: 'OCEANIA',
    phonePrefix: '+61',
  },
];

export const MACHINE_MODELS: MachineModel[] = [
  {
    name: 'CM05S',
    empanadasPerHour: 1600,
    prices: {
      CO: { currency: 'COP', amount: 34886280 },
      CL: { currency: 'USD', amount: 10285 },
      AMERICA: { currency: 'USD', amount: 9885 },
      USA: { currency: 'USD', amount: 10873 },
      EUROPA: { currency: 'USD', amount: 10285 },
      OCEANIA: { currency: 'USD', amount: 9256 },
    },
    capabilities: [
      'empanadas-trigo',
      'empanadas-maiz',
      'arepas',
      'arepas-rellenas',
      'aborrajados',
      'pasteles',
    ],
  },
  {
    name: 'CM06',
    empanadasPerHour: 500,
    prices: {
      CO: { currency: 'COP', amount: 13026822 },
      CL: { currency: 'USD', amount: 4383 },
      AMERICA: { currency: 'USD', amount: 4133 },
      USA: { currency: 'USD', amount: 4546 },
      EUROPA: { currency: 'USD', amount: 4249 },
      OCEANIA: { currency: 'USD', amount: 3824 },
    },
    capabilities: ['empanadas-maiz', 'arepas'],
  },
  {
    name: 'CM06B',
    empanadasPerHour: 500,
    prices: {
      CO: { currency: 'COP', amount: 17892000 },
      CL: { currency: 'USD', amount: 5684 },
      AMERICA: { currency: 'USD', amount: 5434 },
      USA: { currency: 'USD', amount: 5977 },
      EUROPA: { currency: 'USD', amount: 5550 },
      OCEANIA: { currency: 'USD', amount: 4995 },
    },
    capabilities: [
      'empanadas-maiz',
      'arepas',
      'arepas-rellenas',
      'patacones',
      'aborrajados',
      'pasteles',
    ],
  },
  {
    name: 'CM07',
    empanadasPerHour: 400,
    prices: {
      CO: { currency: 'COP', amount: 15450000 },
      CL: { currency: 'USD', amount: 5031 },
      AMERICA: { currency: 'USD', amount: 4781 },
      USA: { currency: 'USD', amount: 5259 },
      EUROPA: { currency: 'USD', amount: 4897 },
      OCEANIA: { currency: 'USD', amount: 4407 },
    },
    capabilities: ['empanadas-trigo'],
  },
  {
    name: 'CM08',
    empanadasPerHour: 500,
    prices: {
      CO: { currency: 'COP', amount: 19252296 },
      CL: { currency: 'USD', amount: 6048 },
      AMERICA: { currency: 'USD', amount: 5798 },
      USA: { currency: 'USD', amount: 6377 },
      EUROPA: { currency: 'USD', amount: 5914 },
      OCEANIA: { currency: 'USD', amount: 5322 },
    },
    capabilities: [
      'empanadas-trigo',
      'empanadas-maiz',
      'arepas',
      'arepas-rellenas',
      'patacones',
      'aborrajados',
      'pasteles',
    ],
  },
];

export const MACHINE_PRICES: MachinePrice[] = MACHINE_MODELS.map((model) => ({
  model: model.name,
  prices: model.prices,
}));

// Asumimos 5 días a la semana * 4 semanas = 20 días productivos al mes
export const DAYS_PER_MONTH = 20;
