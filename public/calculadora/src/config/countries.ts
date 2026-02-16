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

export const CAPABILITY_LABELS: Record<ProductCapability, string> = {
  'empanadas-trigo': 'Empanadas de trigo',
  'empanadas-maiz': 'Empanadas de maíz',
  arepas: 'Arepas',
  'arepas-rellenas': 'Arepas rellenas / Pupusas',
  patacones: 'Patacones / Tostones',
  aborrajados: 'Aborrajados',
  pasteles: 'Pasteles',
};

interface MachineMedia {
  photos: string[];
  video: string;
}

export interface MachineModel {
  name: string;
  empanadasPerHour: number;
  prices: RegionPrices;
  capabilities: ProductCapability[];
  media: MachineMedia;
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
      CL: { currency: 'USD', amount: 11461 },
      AMERICA: { currency: 'USD', amount: 11061 },
      USA: { currency: 'USD', amount: 12167 },
      EUROPA: { currency: 'USD', amount: 11461 },
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
    media: {
      photos: [
        'https://maquiempanadas.com/wp-content/uploads/2021/08/cm05s.jpg',
        'https://maquiempanadas.com/wp-content/uploads/2021/08/CM05S_1-600x600-1.jpg',
        'https://maquiempanadas.com/wp-content/uploads/2021/08/CM05S_2.jpg',
        'https://maquiempanadas.com/wp-content/uploads/2021/08/CM05S_3-600x600-1.jpg',
      ],
      video: 'https://youtu.be/Sm2gIbKSoMQ',
    },
  },
  {
    name: 'CM05i',
    empanadasPerHour: 1600,
    prices: {
      CO: { currency: 'COP', amount: 38479999 },
      CL: { currency: 'USD', amount: 12518 },
      AMERICA: { currency: 'USD', amount: 12118 },
      USA: { currency: 'USD', amount: 13329 },
      EUROPA: { currency: 'USD', amount: 12518 },
      OCEANIA: { currency: 'USD', amount: 11266 },
    },
    capabilities: [
      'empanadas-trigo',
      'empanadas-maiz',
      'arepas',
      'arepas-rellenas',
      'aborrajados',
      'pasteles',
    ],
    media: {
      photos: [
        'https://maquiempanadas.com/wp-content/uploads/2021/08/cm05s.jpg',
        'https://maquiempanadas.com/wp-content/uploads/2021/08/CM05S_1-600x600-1.jpg',
        'https://maquiempanadas.com/wp-content/uploads/2021/08/CM05S_2.jpg',
        'https://maquiempanadas.com/wp-content/uploads/2021/08/CM05S_3-600x600-1.jpg',
      ],
      video: 'https://youtu.be/Sm2gIbKSoMQ',
    },
  },
  {
    name: 'CM06',
    empanadasPerHour: 500,
    prices: {
      CO: { currency: 'COP', amount: 13026822 },
      CL: { currency: 'USD', amount: 4731 },
      AMERICA: { currency: 'USD', amount: 4481 },
      USA: { currency: 'USD', amount: 4930 },
      EUROPA: { currency: 'USD', amount: 4597 },
      OCEANIA: { currency: 'USD', amount: 3824 },
    },
    capabilities: ['empanadas-maiz', 'arepas'],
    media: {
      photos: [
        'https://maquiempanadas.com/wp-content/uploads/2025/02/cm06.webp',
        'https://maquiempanadas.com/wp-content/uploads/2025/02/CM06-2.webp',
        'https://maquiempanadas.com/wp-content/uploads/2025/02/CM06-3.webp',
        'https://maquiempanadas.com/wp-content/uploads/2025/02/CM06-4.webp',
      ],
      video: 'https://www.youtube.com/watch?v=lBZtriCUheA',
    },
  },
  {
    name: 'CM06B',
    empanadasPerHour: 500,
    prices: {
      CO: { currency: 'COP', amount: 17892000 },
      CL: { currency: 'USD', amount: 6162 },
      AMERICA: { currency: 'USD', amount: 5912 },
      USA: { currency: 'USD', amount: 6504 },
      EUROPA: { currency: 'USD', amount: 6028 },
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
    media: {
      photos: [
        'https://maquiempanadas.com/wp-content/uploads/2025/02/CM06B.webp',
        'https://maquiempanadas.com/wp-content/uploads/2025/02/cm06b-4.webp',
        'https://maquiempanadas.com/wp-content/uploads/2025/02/cmo6b-3.webp',
        'https://maquiempanadas.com/wp-content/uploads/2025/02/CMO6B-2.webp',
      ],
      video: 'https://youtu.be/82jVYLarT7I',
    },
  },
  {
    name: 'CM07',
    empanadasPerHour: 400,
    prices: {
      CO: { currency: 'COP', amount: 15450000 },
      CL: { currency: 'USD', amount: 5444 },
      AMERICA: { currency: 'USD', amount: 5194 },
      USA: { currency: 'USD', amount: 5714 },
      EUROPA: { currency: 'USD', amount: 5310 },
      OCEANIA: { currency: 'USD', amount: 4407 },
    },
    capabilities: ['empanadas-trigo'],
    media: {
      photos: [
        'https://maquiempanadas.com/wp-content/uploads/2025/02/CM07.webp',
        'https://maquiempanadas.com/wp-content/uploads/2025/02/CM07_2.webp',
        'https://maquiempanadas.com/wp-content/uploads/2025/02/cm07-3.webp',
        'https://maquiempanadas.com/wp-content/uploads/2025/02/cm07-4.webp',
      ],
      video: 'https://youtu.be/s_6c31nwSdw',
    },
  },
  {
    name: 'CM08',
    empanadasPerHour: 500,
    prices: {
      CO: { currency: 'COP', amount: 19252296 },
      CL: { currency: 'USD', amount: 6562 },
      AMERICA: { currency: 'USD', amount: 6312 },
      USA: { currency: 'USD', amount: 6944 },
      EUROPA: { currency: 'USD', amount: 6428 },
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
    media: {
      photos: [
        'https://maquiempanadas.com/wp-content/uploads/2025/02/CM08_1.webp',
        'https://maquiempanadas.com/wp-content/uploads/2025/02/CM08-2.webp',
        'https://maquiempanadas.com/wp-content/uploads/2025/02/CM08-3.webp',
        'https://maquiempanadas.com/wp-content/uploads/2025/02/CM08-4.webp',
      ],
      video: 'https://youtu.be/ytGbSxvwOJY',
    },
  },
];

export const MACHINE_PRICES: MachinePrice[] = MACHINE_MODELS.map((model) => ({
  model: model.name,
  prices: model.prices,
}));

// Asumimos 5 días a la semana * 4 semanas = 20 días productivos al mes
export const DAYS_PER_MONTH = 20;
