/*
  # Create Quiz Leads Table
  
  1. New Tables
    - `quiz_leads`
      - `id` (uuid, primary key)
      - `country` (text) - País del usuario
      - `currency` (text) - Moneda utilizada
      - `manual_daily_production` (integer) - Empanadas por día actuales
      - `manual_empanadas_per_hour` (integer) - Productividad manual
      - `machine_empanadas_per_hour` (integer) - Productividad con máquina
      - `hourly_wage` (numeric) - Salario por hora
      - `daily_hours` (integer) - Horas trabajadas al día
      - `machine_cost` (numeric) - Costo de la máquina
      - `phone_number` (text) - WhatsApp del lead
      - `manual_cost_per_unit` (numeric) - Costo manual por empanada
      - `machine_cost_per_unit` (numeric) - Costo con máquina por empanada
      - `savings_per_unit` (numeric) - Ahorro por empanada
      - `daily_savings` (numeric) - Ahorro diario
      - `monthly_savings` (numeric) - Ahorro mensual
      - `annual_savings` (numeric) - Ahorro anual
      - `payback_months` (numeric) - Meses para recuperar inversión
      - `efficiency_increase_percent` (numeric) - Incremento de eficiencia
      - `created_at` (timestamptz) - Fecha de creación
      
  2. Security
    - Enable RLS on `quiz_leads` table
    - Add policy for inserting leads (public access for lead generation)
    - Add policy for reading leads (authenticated users only)
*/

CREATE TABLE IF NOT EXISTS quiz_leads (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  country text NOT NULL,
  currency text NOT NULL,
  manual_daily_production integer NOT NULL,
  manual_empanadas_per_hour integer NOT NULL,
  machine_empanadas_per_hour integer NOT NULL,
  hourly_wage numeric NOT NULL,
  daily_hours integer NOT NULL,
  machine_cost numeric NOT NULL,
  phone_number text NOT NULL,
  manual_cost_per_unit numeric NOT NULL,
  machine_cost_per_unit numeric NOT NULL,
  savings_per_unit numeric NOT NULL,
  daily_savings numeric NOT NULL,
  monthly_savings numeric NOT NULL,
  annual_savings numeric NOT NULL,
  payback_months numeric NOT NULL,
  efficiency_increase_percent numeric NOT NULL,
  created_at timestamptz DEFAULT now()
);

ALTER TABLE quiz_leads ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Anyone can insert quiz leads"
  ON quiz_leads
  FOR INSERT
  TO anon
  WITH CHECK (true);

CREATE POLICY "Authenticated users can read all leads"
  ON quiz_leads
  FOR SELECT
  TO authenticated
  USING (true);