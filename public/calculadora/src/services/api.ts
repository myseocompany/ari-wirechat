import { calculateROI, type CalculationResults, type QuizData } from '../utils/calculations';

const DEFAULT_API_URL = 'https://api.maquiempanadas.com';

const API_URL = import.meta.env.VITE_CRM_API_URL || DEFAULT_API_URL;
const CALCULATOR_ENDPOINT =
  import.meta.env.VITE_CALCULATOR_ENDPOINT ||
  import.meta.env.VITE_ESCALABLE_ENDPOINT ||
  `${API_URL}/api/quizzes/escalable`;

type QuizAnswer = {
  question_id: number;
  question_meta_id: number;
  answer_meta_id: number;
  score: number;
  comment?: string;
  answer_text: string;
  question_text: string;
};

const questionMeta = {
  region: { question_id: 30009, question_meta_id: 30009, answer_meta_id: 30009, label: 'Región geográfica' },
  production: { question_id: 30010, question_meta_id: 30010, answer_meta_id: 30010, label: 'Producción diaria' },
  manualRate: { question_id: 30011, question_meta_id: 30011, answer_meta_id: 30011, label: 'Empanadas manual por hora' },
  machineRate: { question_id: 30012, question_meta_id: 30012, answer_meta_id: 30012, label: 'Empanadas con máquina por hora' },
  wage: { question_id: 30013, question_meta_id: 30013, answer_meta_id: 30013, label: 'Salario por hora' },
  machineCost: { question_id: 30014, question_meta_id: 30014, answer_meta_id: 30014, label: 'Máquina seleccionada' },
  dough: { question_id: 30015, question_meta_id: 30015, answer_meta_id: 30015, label: 'Masa' },
  product: { question_id: 30016, question_meta_id: 30016, answer_meta_id: 30016, label: 'Producto principal' },
  payback: { question_id: 30017, question_meta_id: 30017, answer_meta_id: 30017, label: 'Meses de recuperación' },
  savings: { question_id: 30018, question_meta_id: 30018, answer_meta_id: 30018, label: 'Ahorro mensual' },
  annual: { question_id: 30019, question_meta_id: 30019, answer_meta_id: 30019, label: 'Ahorro/ingreso anual' },
};

const stageFromPayback = (months: number) => {
  if (!months || !isFinite(months)) return 'Desconocido';
  if (months <= 6) return 'Recupera inversión en <6 meses';
  if (months <= 12) return 'Recupera inversión en ~1 año';
  return 'Recupera inversión en >1 año';
};

const scoreFromPayback = (months: number) => {
  if (!months || !isFinite(months)) return 0;
  const score = 100 - Math.min(months * 5, 100);
  return Math.max(0, Math.round(score));
};

export interface LeadAnswer {
  question_id: number;
  question_meta_id: number;
  answer_meta_id: number;
  score: number;
  comment?: string | null;
  answer_text: string;
  question_text: string;
}

export interface LeadPayload {
  phone: string;
  final_score: number;
  stage: string;
  quiz_meta_id: number;
  answers: LeadAnswer[];
  completed_at: string;
  invitation_eligible: boolean;
}

export function buildLeadPayload(quizData: Partial<QuizData>): LeadPayload | null {
  if (!quizData.phone) return null;

  const required =
    quizData.manualDailyProduction &&
    quizData.manualEmpanadasPerHour &&
    quizData.machineEmpanadasPerHour &&
    quizData.hourlyWage &&
    quizData.machineCost;

  if (!required) return null;

  const results = calculateROI(quizData as QuizData);

  const answers: QuizAnswer[] = [
    {
      ...questionMeta.region,
      score: 1,
      comment: undefined,
      answer_text: quizData.priceRegion || quizData.country || 'No especificado',
      question_text: questionMeta.region.label,
    },
    {
      ...questionMeta.production,
      score: 1,
      comment: undefined,
      answer_text: `${quizData.manualDailyProduction} emp/día`,
      question_text: questionMeta.production.label,
    },
    {
      ...questionMeta.manualRate,
      score: 1,
      comment: undefined,
      answer_text: `${quizData.manualEmpanadasPerHour} emp/h`,
      question_text: questionMeta.manualRate.label,
    },
    {
      ...questionMeta.machineRate,
      score: 1,
      comment: undefined,
      answer_text: `${quizData.machineEmpanadasPerHour} emp/h`,
      question_text: questionMeta.machineRate.label,
    },
    {
      ...questionMeta.wage,
      score: 1,
      comment: undefined,
      answer_text: `${quizData.hourlyWage} ${quizData.currency || ''}`.trim(),
      question_text: questionMeta.wage.label,
    },
    {
      ...questionMeta.machineCost,
      score: 1,
      comment: undefined,
      answer_text: quizData.machineModel || 'No especificado',
      question_text: questionMeta.machineCost.label,
    },
    {
      ...questionMeta.dough,
      score: 1,
      comment: undefined,
      answer_text: quizData.doughType || 'No especificado',
      question_text: questionMeta.dough.label,
    },
    {
      ...questionMeta.product,
      score: 1,
      comment: undefined,
      answer_text: quizData.productType || 'No especificado',
      question_text: questionMeta.product.label,
    },
    {
      ...questionMeta.payback,
      score: 1,
      comment: undefined,
      answer_text: `${results.paybackMonths.toFixed(1)} meses`,
      question_text: questionMeta.payback.label,
    },
    {
      ...questionMeta.savings,
      score: 1,
      comment: undefined,
      answer_text: `${results.monthlySavings.toFixed(2)} ${quizData.currency || ''}`.trim(),
      question_text: questionMeta.savings.label,
    },
    {
      ...questionMeta.annual,
      score: 1,
      comment: undefined,
      answer_text: `${results.annualSavings.toFixed(2)} ${quizData.currency || ''}`.trim(),
      question_text: questionMeta.annual.label,
    },
  ];

  return {
    phone: quizData.phone,
    final_score: scoreFromPayback(results.paybackMonths),
    stage: stageFromPayback(results.paybackMonths),
    quiz_meta_id: 3000,
    answers,
    completed_at: new Date().toISOString(),
    invitation_eligible: results.paybackMonths <= 12,
  };
}

export async function sendQuizLead(
  quizData: Partial<QuizData>,
): Promise<{ response: Response; payload: LeadPayload } | undefined> {
  const payload = buildLeadPayload(quizData);
  if (!payload) return;

  const res = await fetch(CALCULATOR_ENDPOINT, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  });

  return { response: res, payload };
}
