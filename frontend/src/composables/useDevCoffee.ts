import { ref } from 'vue'
import type { ApiProblemDetails, Consumption, Drink, EcoMetrics } from '../types/devcoffee'

const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api').replace(/\/$/, '')

export function useDevCoffee() {
  const drinks = ref<Drink[]>([])
  const consumptions = ref<Consumption[]>([])
  const loading = ref(false)
  const submitting = ref(false)
  const deletingId = ref<number | null>(null)
  const errorTitle = ref('')
  const errorMessage = ref('')
  const successMessage = ref('')
  const ecoMetrics = ref<EcoMetrics>({ responseTime: null, payloadSize: null, co2: null })

  const captureEcoHeaders = (response: Response): void => {
    ecoMetrics.value = {
      responseTime: response.headers.get('X-Response-Time-Ms'),
      payloadSize: response.headers.get('X-Payload-Size-Bytes'),
      co2: response.headers.get('X-Estimated-CO2-Grams'),
    }
  }

  const clearAlerts = (): void => {
    errorTitle.value = ''
    errorMessage.value = ''
    successMessage.value = ''
  }

  const displayProblem = async (response: Response, fallback: string): Promise<void> => {
    let problem: Partial<ApiProblemDetails> = {}
    try {
      problem = await response.json() as ApiProblemDetails
    } catch {
      // Une API peut renvoyer un corps non JSON en cas de panne ; on conserve un message exploitable.
    }
    errorTitle.value = problem.title || `Erreur HTTP ${response.status}`
    errorMessage.value = problem.detail || fallback
  }

  const fetchDrinks = async (): Promise<void> => {
    try {
      const response = await fetch(`${API_BASE_URL}/drinks`, { headers: { Accept: 'application/json' } })
      captureEcoHeaders(response)
      if (!response.ok) {
        await displayProblem(response, 'Impossible de charger le catalogue des boissons.')
        return
      }
      drinks.value = await response.json() as Drink[]
    } catch {
      errorTitle.value = 'API indisponible'
      errorMessage.value = `Impossible de contacter ${API_BASE_URL}. Vérifie que le backend PHP et MySQL sont démarrés.`
    }
  }

  const fetchConsumptions = async (): Promise<void> => {
    try {
      const response = await fetch(`${API_BASE_URL}/consumptions`, { headers: { Accept: 'application/json' } })
      captureEcoHeaders(response)
      if (!response.ok) {
        await displayProblem(response, 'Impossible de charger l’historique des consommations.')
        return
      }
      consumptions.value = await response.json() as Consumption[]
    } catch {
      errorTitle.value = 'API indisponible'
      errorMessage.value = `Impossible de contacter ${API_BASE_URL}. Vérifie que le backend PHP et MySQL sont démarrés.`
    }
  }

  const refreshAll = async (): Promise<void> => {
    loading.value = true
    await Promise.all([fetchDrinks(), fetchConsumptions()])
    loading.value = false
  }

  const recordConsumption = async (payload: { drinkId: number; developerName: string }): Promise<void> => {
    clearAlerts()
    submitting.value = true
    try {
      const response = await fetch(`${API_BASE_URL}/consumptions`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json, application/problem+json' },
        body: JSON.stringify(payload),
      })
      captureEcoHeaders(response)
      if (!response.ok) {
        await displayProblem(response, 'La consommation n’a pas pu être enregistrée.')
        return
      }
      successMessage.value = 'Consommation enregistrée.'
      await fetchConsumptions()
    } catch {
      errorTitle.value = 'API indisponible'
      errorMessage.value = 'Impossible d’enregistrer la consommation. Vérifie la connexion au backend.'
    } finally {
      submitting.value = false
    }
  }

  const deleteConsumption = async (item: Consumption): Promise<void> => {
    clearAlerts()
    deletingId.value = item.id
    try {
      // Le lien de suppression vient directement de _links.delete.href (HATEOAS).
      const response = await fetch(item._links.delete.href, {
        method: item._links.delete.method || 'DELETE',
        headers: { Accept: 'application/json, application/problem+json' },
      })
      captureEcoHeaders(response)
      if (!response.ok) {
        await displayProblem(response, 'La consommation n’a pas pu être supprimée.')
        return
      }
      successMessage.value = 'Consommation supprimée.'
      await fetchConsumptions()
    } catch {
      errorTitle.value = 'API indisponible'
      errorMessage.value = 'Impossible de supprimer la consommation. Vérifie la connexion au backend.'
    } finally {
      deletingId.value = null
    }
  }

  return {
    drinks,
    consumptions,
    loading,
    submitting,
    deletingId,
    errorTitle,
    errorMessage,
    successMessage,
    ecoMetrics,
    refreshAll,
    recordConsumption,
    deleteConsumption,
    clearAlerts,
  }
}
