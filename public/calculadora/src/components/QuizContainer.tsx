import { useState } from 'react';
import { useQuizState } from '../hooks/useQuizState';
import CountryStep from './steps/CountryStep';
import ManualProductionStep from './steps/ManualProductionStep';
import ManualRateStep from './steps/ManualRateStep';
import MachineFitStep from './steps/MachineFitStep';
import HourlyWageStep from './steps/HourlyWageStep';
import PartialResults from './results/PartialResults';
import FullReport from './results/FullReport';
import ProgressBar from './ProgressBar';
import LogoMQE from '../img/Logo_MQE_normal-40px.png';
import LogoCongeladosCriss from '../img/customers/logo_congelados_criss.png';
import LogoEmpanadasDonJose from '../img/customers/logo_empanadas_don_jose.png';
import LogoEmpanadasElMachetico from '../img/customers/logo_empanadas_el_machetico.png';
import LogoJosePan from '../img/customers/logo_jose_pan.png';
import LogoLaLocura from '../img/customers/logo_la_locura.png';
import LogoPaparepa from '../img/customers/logo_paparepa.png';
import { sendQuizLead, buildLeadPayload } from '../services/api';
import { calculateROI, type QuizData } from '../utils/calculations';

const CUSTOMER_LOGOS = [
  { src: LogoCongeladosCriss, alt: 'Congelados Criss' },
  { src: LogoEmpanadasDonJose, alt: 'Empanadas Don José' },
  { src: LogoEmpanadasElMachetico, alt: 'Empanadas El Machetico' },
  { src: LogoJosePan, alt: 'José Pan' },
  { src: LogoLaLocura, alt: 'La Locura' },
  { src: LogoPaparepa, alt: 'Paparepa' },
] as const;

const FLAG_COUNTRIES = [
  'CO',
  'US',
  'CA',
  'CH',
  'EC',
  'HN',
  'CR',
  'VE',
  'FR',
  'ES',
  'PA',
  'AR',
  'AU',
  'GB',
  'AT',
  'CL',
  'TT',
  'IT',
  'GT',
  'MX',
  'NL',
  'DE',
  'PR',
  'BE',
  'CW',
  'DO',
  'AW',
  'BO',
  'NO',
  'LU',
  'AF',
  'DM',
  'PE',
  'PT',
  'UY',
  'DK',
  'LA',
] as const;

const toFlagEmoji = (countryCode: string) =>
  countryCode
    .toUpperCase()
    .split('')
    .map((char) => String.fromCodePoint(0x1f1e6 + char.charCodeAt(0) - 65))
    .join('');

export default function QuizContainer() {
  const [sending, setSending] = useState(false);
  const [sent, setSent] = useState(false);
  const [payloadPreview, setPayloadPreview] = useState<string | null>(null);
  const [sendResult, setSendResult] = useState<string | null>(null);
  const [started, setStarted] = useState(false);
  const { currentStep, quizData, updateData, nextStep, previousStep, goToStep } = useQuizState();
  const buildDate = new Date(__BUILD_DATE__);
  const formattedBuildDate = new Intl.DateTimeFormat('es-CO', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(buildDate);

  const totalSteps = 5;
  const stepNumber = {
    country: 1,
    manualProduction: 2,
    manualRate: 3,
    machineFit: 4,
    hourlyWage: 5,
    partialResults: 6,
    fullReport: 7,
  }[currentStep];

  const sendCRMIfPossible = async (data: Partial<QuizData>) => {
    setSending(true);
    const preview = buildLeadPayload(data);
    if (preview) {
      setPayloadPreview(JSON.stringify(preview, null, 2));
      try {
        const result = await sendQuizLead(preview);
        if (result) {
          const { response } = result;
          const text = await response.text();
          if (response.ok) {
            setSent(true);
            setSendResult(`Enviado al CRM correctamente. Status ${response.status}: ${text || 'OK'}`);
          } else {
            setSendResult(`Error al enviar (status ${response.status}): ${text}`);
          }
        }
      } catch (err) {
        console.error('Error enviando lead', err);
        setSendResult('Error al enviar al CRM. Revisa la consola.');
      }
    } else {
      setSendResult('No se envió: faltan datos necesarios.');
    }
    setSending(false);
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50">
      <div className={`container mx-auto px-4 py-8 max-w-3xl ${!started ? 'pb-28' : ''}`}>
        <div className="text-center mb-8 space-y-3">
          <div className="flex justify-center">
            <img src={LogoMQE} alt="MQE" className="h-10 w-auto" />
          </div>
          <div className="space-y-2">
            <h1 className="text-4xl font-bold text-gray-900 mb-2">
              Atrévete a Crecer
            </h1>
            <p className="text-lg text-gray-600" >
              ¿Cuánto ganarías si pudieras aceptar TODOS los pedidos que llegan?
            </p>
          </div>
        </div>

        {!started && (
          <div className="rounded-2xl border border-slate-200 bg-white/90 shadow-sm p-5 space-y-3">
            <p className="text-base font-semibold text-slate-900">
              ¿Rechazas pedidos por falta de capacidad?
            </p>
            <p className="text-sm text-slate-600">
              No estás solo. El 73% de nuestros clientes llegó porque:
            </p>
            <div className="mb-6 space-y-3 rounded-xl border border-red-100 bg-red-50/50 p-4 text-sm text-red-900">
              <p>❌ No daban abasto con pedidos grandes</p>
              <p>❌ Dependían de &quot;maestros empanadores&quot; difíciles de reemplazar</p>
              <p>❌ La producción manual les limitaba crecer</p>
            </div>
            <div className="mb-6 rounded-xl border border-green-100 bg-green-50/50 p-3 text-center text-sm font-semibold text-green-900">
              HOY producen 10X más con la misma cantidad de personas
            </div>
            <p className="text-sm text-slate-600">
              Esta calculadora te muestra en números reales:
            </p>
            <div className="grid grid-cols-1 gap-3 text-sm text-slate-800 sm:grid-cols-2">
              <div className="rounded-lg border border-slate-200 bg-blue-50/40 p-3">
                💰 Ahorro mensual (En mano de obra)
              </div>
              <div className="rounded-lg border border-slate-200 bg-blue-50/40 p-3">
                📈 Aumento de capacidad (Cuántas más podrías vender)
              </div>
              <div className="rounded-lg border border-slate-200 bg-blue-50/40 p-3">
                ⏱️ Tiempo de ROI (Meses para recuperar inversión)
              </div>
              <div className="rounded-lg border border-slate-200 bg-blue-50/40 p-3">
                🚀 Crecimiento sin personal (Escala sin contratar más)
              </div>
            </div>
          
          <p className="text-xs uppercase tracking-wide text-slate-400">Usada por más de 500 negocios en 42 países</p>
          <div className="flex flex-wrap justify-center gap-2 text-2xl" aria-label="Banderas de países representados">
            {FLAG_COUNTRIES.map((code) => (
              <span key={code} role="img" aria-label={`Bandera ${code}`}>
                {toFlagEmoji(code)}
              </span>
            ))}
          </div>
          <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
            Algunos clientes en Colombia y el mundo
          </p>
          <div className="grid grid-cols-3 gap-4 items-center justify-center sm:grid-cols-6">
            {CUSTOMER_LOGOS.map((logo) => (
              <div key={logo.alt} className="flex items-center justify-center">
                <img src={logo.src} alt={logo.alt} className="h-8 max-h-10 object-contain" />
              </div>
            ))}
          </div>
          <p className="text-[11px] text-slate-500 italic">
            💡 Cálculos basados en datos reales de más de 500 clientes. Tus resultados pueden variar según tu operación.
          </p>
          </div>
        )}
        {!started && (
          <div className="mt-6 text-center text-xs text-slate-500">
            💡 Toma 2 minutos. Resultados instantáneos.
          </div>
        )}
        {!started && (
          <div className="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 backdrop-blur">
            <div className="container mx-auto max-w-3xl px-4 py-3">
              <button
                type="button"
                className="w-full rounded-xl bg-orange-500 hover:bg-orange-600 px-8 py-5 text-lg font-bold text-white transition shadow-xl hover:shadow-2xl transform hover:scale-105"
                onClick={() => setStarted(true)}
              >
                CALCULAR MI AHORRO →
              </button>
            </div>
          </div>
        )}
        {started && (
          <>
            {stepNumber <= totalSteps && (
              <ProgressBar current={stepNumber} total={totalSteps} />
            )}

            <div className="bg-white rounded-2xl shadow-xl p-8 mt-8">
              {currentStep === 'country' && (
                <CountryStep
                  value={quizData.country}
                  onBack={() => setStarted(false)}
                  onSelect={(country) => {
                    updateData('country', country.name);
                    updateData('currency', country.currency);
                    updateData('currencySymbol', country.currencySymbol);
                    updateData('priceRegion', country.priceRegion);
                    updateData('phonePrefix', country.phonePrefix);
                    updateData('hourlyWage', country.suggestedHourlyWage);
                    nextStep();
                  }}
                />
              )}

              {currentStep === 'manualProduction' && (
                <ManualProductionStep
                  value={quizData.manualDailyProduction}
                  onNext={(value) => {
                    updateData('manualDailyProduction', value);
                    if (quizData.manualEmpanadasPerHour) {
                      updateData('dailyHours', value / quizData.manualEmpanadasPerHour);
                    }
                    nextStep();
                  }}
                  onBack={previousStep}
                />
              )}

              {currentStep === 'manualRate' && (
                <ManualRateStep
                  value={quizData.manualEmpanadasPerHour}
                  onNext={(value) => {
                    updateData('manualEmpanadasPerHour', value);
                    if (quizData.manualDailyProduction) {
                      updateData('dailyHours', quizData.manualDailyProduction / value);
                    }
                    nextStep();
                  }}
                  onBack={previousStep}
                />
              )}

              {currentStep === 'machineFit' && (
                <MachineFitStep
                  doughType={quizData.doughType as 'trigo' | 'maiz' | undefined}
                  productType={
                    quizData.productType as 'empanadas' | 'empanadas-otros' | 'otros' | undefined
                  }
                  machineModel={quizData.machineModel}
                  priceRegion={quizData.priceRegion}
                  onNext={(data) => {
                    updateData('doughType', data.doughType);
                    updateData('productType', data.productType);
                    updateData('machineModel', data.machineModel);
                    updateData('machineEmpanadasPerHour', data.machineEmpanadasPerHour);
                    updateData('machineCost', data.machineCost);
                    nextStep();
                  }}
                  onBack={previousStep}
                />
              )}

              {currentStep === 'hourlyWage' && (
                <HourlyWageStep
                  value={quizData.hourlyWage}
                  currency={quizData.currencySymbol || '$'}
                  onNext={(value) => {
                    updateData('hourlyWage', value);
                    nextStep();
                  }}
                  onBack={previousStep}
                />
              )}

              {currentStep === 'partialResults' && (
                <PartialResults
                  quizData={quizData}
                  onNext={(phone) => {
                    if (phone) {
                      updateData('phone', phone);
                    }
                    nextStep();
                  }}
                />
              )}

              {currentStep === 'fullReport' && (
                <FullReport quizData={quizData} onRestart={() => goToStep('country')} />
              )}
            </div>
        </>
      )}
        <footer className="mt-8 text-center text-sm text-gray-500 space-y-3">
          <div className="flex justify-center">
            <img src={LogoMQE} alt="MQE" className="h-8 w-auto" />
          </div>
          <div>
            Versión {__APP_VERSION__} · Actualizado {formattedBuildDate}
          </div>
        </footer>
      </div>
    </div>
  );
}
