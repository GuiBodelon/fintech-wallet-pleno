export function useMoney() {
  const formatter = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  })

  function formatCents(amountCents: number | null | undefined): string {
    return formatter.format((amountCents ?? 0) / 100)
  }

  function parseCurrencyToCents(value: string): number | null {
    const trimmedValue = value.trim()

    if (!/^\d+([,.]\d{1,2})?$/.test(trimmedValue)) {
      return null
    }

    const [reais, cents = ''] = trimmedValue.replace(',', '.').split('.')

    return Number(reais) * 100 + Number(cents.padEnd(2, '0'))
  }

  return {
    formatCents,
    parseCurrencyToCents,
  }
}
