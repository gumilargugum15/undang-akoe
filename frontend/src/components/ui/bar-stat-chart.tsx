import { Bar, BarChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from "recharts";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

/** Vivid violet, matches the --primary/--chart-1 brand token. Hardcoded (not `var(--...)`)
 * because SVG presentation attributes don't reliably resolve CSS custom properties. */
const BAR_COLOR = "#7c3aed";

export function BarStatChart({
  title,
  data,
  dataKey,
  labelKey,
  emptyMessage = "Belum ada data.",
}: {
  title: string;
  data: Array<Record<string, string | number>>;
  dataKey: string;
  labelKey: string;
  emptyMessage?: string;
}) {
  return (
    <Card className="rounded-2xl border-none shadow-sm">
      <CardHeader>
        <CardTitle className="text-base">{title}</CardTitle>
      </CardHeader>
      <CardContent className="h-64">
        {data.length === 0 ? (
          <p className="flex h-full items-center justify-center text-sm text-muted-foreground">
            {emptyMessage}
          </p>
        ) : (
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={data} margin={{ left: -20 }}>
              <CartesianGrid strokeDasharray="3 3" vertical={false} className="stroke-border" />
              <XAxis
                dataKey={labelKey}
                tickLine={false}
                axisLine={false}
                fontSize={12}
                className="fill-muted-foreground"
              />
              <YAxis
                allowDecimals={false}
                tickLine={false}
                axisLine={false}
                fontSize={12}
                className="fill-muted-foreground"
              />
              <Tooltip
                cursor={{ fill: "var(--color-muted)" }}
                contentStyle={{
                  borderRadius: 8,
                  fontSize: 12,
                  background: "var(--color-popover)",
                  color: "var(--color-popover-foreground)",
                  border: "1px solid var(--color-border)",
                }}
              />
              <Bar
                dataKey={dataKey}
                radius={[6, 6, 0, 0]}
                fill={BAR_COLOR}
                maxBarSize={48}
                isAnimationActive={false}
              />
            </BarChart>
          </ResponsiveContainer>
        )}
      </CardContent>
    </Card>
  );
}
