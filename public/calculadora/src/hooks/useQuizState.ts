import { useState } from 'react';
import { QuizData } from '../utils/calculations';

export type QuizStep =
  | 'country'
  | 'manualProduction'
  | 'manualRate'
  | 'machineFit'
  | 'hourlyWage'
  | 'partialResults'
  | 'fullReport';

export function useQuizState() {
  const [currentStep, setCurrentStep] = useState<QuizStep>('country');
  const [quizData, setQuizData] = useState<Partial<QuizData>>({});

  const updateData = (field: keyof QuizData, value: string | number) => {
    setQuizData((prev) => ({ ...prev, [field]: value }));
  };

  const nextStep = () => {
    const steps: QuizStep[] = [
      'country',
      'manualProduction',
      'manualRate',
      'machineFit',
      'hourlyWage',
      'partialResults',
      'fullReport',
    ];
    const currentIndex = steps.indexOf(currentStep);
    if (currentIndex < steps.length - 1) {
      setCurrentStep(steps[currentIndex + 1]);
    }
  };

  const previousStep = () => {
    const steps: QuizStep[] = [
      'country',
      'manualProduction',
      'manualRate',
      'machineFit',
      'hourlyWage',
      'partialResults',
      'fullReport',
    ];
    const currentIndex = steps.indexOf(currentStep);
    if (currentIndex > 0) {
      setCurrentStep(steps[currentIndex - 1]);
    }
  };

  const goToStep = (step: QuizStep) => {
    setCurrentStep(step);
  };

  return {
    currentStep,
    quizData,
    updateData,
    nextStep,
    previousStep,
    goToStep,
  };
}
