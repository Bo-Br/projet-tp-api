export interface HateoasLink {
  href: string
  method: string
}

export interface Drink {
  id: number
  name: string
  caffeineMg: number
  volumeMl: number
  _links?: {
    self: HateoasLink
  }
}

export interface Consumption {
  id: number
  developerName: string
  drinkName: string
  caffeineMg: number
  consumedAt: string
  _links: {
    delete: HateoasLink
  }
}

export interface ApiProblemDetails {
  type: string
  title: string
  status: number
  detail: string
}

export interface EcoMetrics {
  responseTime: string | null
  payloadSize: string | null
  co2: string | null
}
