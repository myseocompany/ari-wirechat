# Consultas SQL Útiles

## Análisis de Leads Capturados

### Ver todos los leads ordenados por fecha
```sql
SELECT
  created_at,
  country,
  phone_number,
  payback_months,
  monthly_savings,
  annual_savings
FROM quiz_leads
ORDER BY created_at DESC;
```

### Leads con mejor ROI (payback menor a 6 meses)
```sql
SELECT
  phone_number,
  country,
  payback_months,
  annual_savings,
  efficiency_increase_percent
FROM quiz_leads
WHERE payback_months <= 6
ORDER BY payback_months ASC;
```

### Análisis por país
```sql
SELECT
  country,
  COUNT(*) as total_leads,
  AVG(payback_months) as avg_payback,
  AVG(monthly_savings) as avg_monthly_savings,
  AVG(annual_savings) as avg_annual_savings
FROM quiz_leads
GROUP BY country
ORDER BY total_leads DESC;
```

### Leads del día
```sql
SELECT
  created_at,
  country,
  phone_number,
  payback_months,
  monthly_savings
FROM quiz_leads
WHERE created_at >= CURRENT_DATE
ORDER BY created_at DESC;
```

### Leads de la semana
```sql
SELECT
  country,
  phone_number,
  payback_months,
  annual_savings,
  created_at
FROM quiz_leads
WHERE created_at >= CURRENT_DATE - INTERVAL '7 days'
ORDER BY created_at DESC;
```

### Leads con mayor ahorro mensual
```sql
SELECT
  phone_number,
  country,
  currency,
  monthly_savings,
  annual_savings,
  payback_months
FROM quiz_leads
ORDER BY monthly_savings DESC
LIMIT 20;
```

### Distribución por perfil de negocio
```sql
SELECT
  CASE
    WHEN payback_months <= 6 THEN 'Startup'
    WHEN payback_months <= 12 THEN 'Growth'
    ELSE 'Expanding'
  END as business_profile,
  COUNT(*) as leads_count,
  AVG(annual_savings) as avg_annual_savings
FROM quiz_leads
GROUP BY business_profile
ORDER BY leads_count DESC;
```

### Leads con producción alta (más de 1000 empanadas/día)
```sql
SELECT
  phone_number,
  country,
  manual_daily_production,
  machine_empanadas_per_hour,
  monthly_savings
FROM quiz_leads
WHERE manual_daily_production > 1000
ORDER BY manual_daily_production DESC;
```

### Análisis de eficiencia
```sql
SELECT
  country,
  AVG(manual_empanadas_per_hour) as avg_manual_rate,
  AVG(machine_empanadas_per_hour) as avg_machine_rate,
  AVG(efficiency_increase_percent) as avg_efficiency_gain
FROM quiz_leads
GROUP BY country
ORDER BY avg_efficiency_gain DESC;
```

### Leads con ROI negativo o muy largo
```sql
SELECT
  phone_number,
  country,
  payback_months,
  monthly_savings,
  machine_cost,
  created_at
FROM quiz_leads
WHERE payback_months > 24 OR monthly_savings <= 0
ORDER BY created_at DESC;
```

### Comparación de costos por empanada
```sql
SELECT
  country,
  currency,
  AVG(manual_cost_per_unit) as avg_manual_cost,
  AVG(machine_cost_per_unit) as avg_machine_cost,
  AVG(savings_per_unit) as avg_savings
FROM quiz_leads
GROUP BY country, currency
ORDER BY avg_savings DESC;
```

### Tasa de conversión por hora del día
```sql
SELECT
  EXTRACT(HOUR FROM created_at) as hour_of_day,
  COUNT(*) as leads_count
FROM quiz_leads
GROUP BY hour_of_day
ORDER BY hour_of_day;
```

### Leads únicos (sin duplicados por teléfono)
```sql
SELECT DISTINCT ON (phone_number)
  phone_number,
  country,
  payback_months,
  monthly_savings,
  created_at
FROM quiz_leads
ORDER BY phone_number, created_at DESC;
```

### Resumen estadístico general
```sql
SELECT
  COUNT(*) as total_leads,
  COUNT(DISTINCT country) as countries_count,
  AVG(payback_months) as avg_payback_months,
  MIN(payback_months) as best_payback,
  MAX(payback_months) as worst_payback,
  AVG(monthly_savings) as avg_monthly_savings,
  AVG(annual_savings) as avg_annual_savings,
  AVG(efficiency_increase_percent) as avg_efficiency_increase
FROM quiz_leads;
```

### Leads calientes (mejor payback y mayor ahorro)
```sql
SELECT
  phone_number,
  country,
  payback_months,
  monthly_savings,
  annual_savings,
  created_at
FROM quiz_leads
WHERE payback_months <= 8
  AND monthly_savings > 1000
ORDER BY payback_months ASC, monthly_savings DESC
LIMIT 50;
```

### Análisis de tendencias (por mes)
```sql
SELECT
  DATE_TRUNC('month', created_at) as month,
  COUNT(*) as leads_count,
  AVG(payback_months) as avg_payback,
  AVG(monthly_savings) as avg_savings
FROM quiz_leads
GROUP BY month
ORDER BY month DESC;
```

## Queries para Exportar

### Exportar para CRM (formato CSV-friendly)
```sql
SELECT
  phone_number as "Teléfono",
  country as "País",
  ROUND(payback_months, 1) as "Payback (meses)",
  ROUND(monthly_savings, 2) as "Ahorro Mensual",
  ROUND(annual_savings, 2) as "Ahorro Anual",
  ROUND(efficiency_increase_percent, 1) as "Eficiencia %",
  TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI') as "Fecha"
FROM quiz_leads
ORDER BY created_at DESC;
```

### Exportar leads calientes
```sql
SELECT
  phone_number,
  country,
  payback_months,
  monthly_savings,
  'HOT LEAD' as status
FROM quiz_leads
WHERE payback_months <= 6
  AND monthly_savings > 500
ORDER BY payback_months ASC;
```

## Queries de Mantenimiento

### Eliminar leads de prueba
```sql
-- ¡CUIDADO! Esta query elimina datos permanentemente
DELETE FROM quiz_leads
WHERE phone_number LIKE '%test%'
   OR phone_number LIKE '%prueba%';
```

### Actualizar timezone de registros antiguos
```sql
UPDATE quiz_leads
SET created_at = created_at AT TIME ZONE 'America/Bogota'
WHERE country = 'Colombia'
  AND created_at < '2024-01-01';
```

## Vistas Útiles

### Crear vista de leads calientes
```sql
CREATE VIEW hot_leads AS
SELECT
  phone_number,
  country,
  payback_months,
  monthly_savings,
  annual_savings,
  created_at
FROM quiz_leads
WHERE payback_months <= 8
  AND monthly_savings > 500
ORDER BY created_at DESC;
```

### Crear vista de resumen por país
```sql
CREATE VIEW country_summary AS
SELECT
  country,
  COUNT(*) as total_leads,
  AVG(payback_months) as avg_payback,
  AVG(monthly_savings) as avg_monthly_savings,
  MIN(created_at) as first_lead_date,
  MAX(created_at) as last_lead_date
FROM quiz_leads
GROUP BY country;
```

## Uso desde Supabase

Puedes ejecutar estas queries directamente en:
1. Supabase Dashboard → SQL Editor
2. Tu aplicación usando el cliente de Supabase
3. API REST de Supabase

Ejemplo en JavaScript:
```javascript
const { data, error } = await supabase
  .from('quiz_leads')
  .select('*')
  .gte('payback_months', 0)
  .lte('payback_months', 6)
  .order('created_at', { ascending: false });
```
