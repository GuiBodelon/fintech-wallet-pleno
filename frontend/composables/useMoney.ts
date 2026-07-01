export function useMoney() {
  const formatter = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  })

  function formatCents(amountCents: number | null | undefined): string {
    return formatter.format((amountCents ?? 0) / 100)
  }

  return {
    formatCents,
  }
}
