interface PaybackChartProps {
  monthlySavings: number;
  machineCost: number;
  currencySymbol: string;
}

export default function PaybackChart({
  monthlySavings,
  machineCost,
  currencySymbol,
}: PaybackChartProps) {
  if (monthlySavings <= 0 || machineCost <= 0) {
    return (
      <div className="bg-white border-2 border-gray-200 rounded-xl p-4 text-sm text-gray-700">
        No se puede graficar el payback: falta el costo de la máquina o el ahorro mensual es 0.
      </div>
    );
  }

  const paybackMonths = machineCost / monthlySavings;
  const maxMonths = Math.min(Math.max(Math.ceil(paybackMonths) + 3, 12), 36);

  const points = Array.from({ length: maxMonths + 1 }, (_, i) => ({
    month: i,
    savings: monthlySavings * i,
  }));

  const maxSavings = Math.max(...points.map((p) => p.savings), machineCost);
  const height = 180;
  const width = 360;
  const padding = 32;

  const scaleX = (month: number) =>
    padding + (month / maxMonths) * (width - padding * 2);
  const scaleY = (val: number) =>
    height - padding - (val / maxSavings) * (height - padding * 2);

  const linePath = points
    .map((p, idx) => `${idx === 0 ? 'M' : 'L'} ${scaleX(p.month)} ${scaleY(p.savings)}`)
    .join(' ');

  const paybackX = scaleX(paybackMonths);
  const paybackY = scaleY(machineCost);

  return (
    <div className="bg-white border-2 border-gray-200 rounded-xl p-4">
      <div className="flex items-center justify-between mb-2 text-sm text-gray-700">
        <span>Proyección de payback</span>
        <span>
          Punto de equilibrio: {paybackMonths.toFixed(1)} meses
        </span>
      </div>
      <svg viewBox={`0 0 ${width} ${height}`} className="w-full h-44">
        <defs>
          <linearGradient id="paybackFill" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0%" stopColor="#22c55e" stopOpacity="0.2" />
            <stop offset="100%" stopColor="#22c55e" stopOpacity="0" />
          </linearGradient>
        </defs>
        <line
          x1={padding}
          y1={scaleY(machineCost)}
          x2={width - padding}
          y2={scaleY(machineCost)}
          stroke="#e5e7eb"
          strokeDasharray="6 6"
        />
        <path d={`${linePath} V ${height - padding} H ${padding} Z`} fill="url(#paybackFill)" />
        <path d={linePath} fill="none" stroke="#22c55e" strokeWidth={3} />
        <circle cx={paybackX} cy={paybackY} r={5} fill="#16a34a" />
        <text
          x={paybackX + 8}
          y={paybackY - 6}
          fontSize="10"
          fill="#065f46"
        >
          Payback
        </text>
        <text
          x={padding}
          y={height - padding + 14}
          fontSize="10"
          fill="#6b7280"
        >
          0
        </text>
        <text
          x={width - padding - 10}
          y={height - padding + 14}
          fontSize="10"
          fill="#6b7280"
        >
          {maxMonths}m
        </text>
        <text
          x={padding - 6}
          y={scaleY(machineCost) - 4}
          fontSize="10"
          fill="#6b7280"
        >
          {currencySymbol}{machineCost.toLocaleString()}
        </text>
      </svg>
      <p className="text-xs text-gray-600 mt-2">
        La línea verde muestra el ahorro acumulado; el punto marca cuando el ahorro supera el costo de la máquina.
      </p>
    </div>
  );
}
