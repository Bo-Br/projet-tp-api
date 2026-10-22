import { ref } from 'vue';
import type { Station, ApiProblemDetails, EcoMetrics } from '../types/ecopark';
const API_BASE_URL = 'http://127.0.0.1:5500/api';
export function useEcoPark() {
const stations = ref<Station[]>([]);
const errorTitle = ref<string>('');
const errorMessage = ref<string>('');
const ecoMetrics = ref<EcoMetrics>({
responseTime: null,
payloadSize: null,
co2: null,
});
const captureEcoHeaders = (response: Response): void => {
ecoMetrics.value = {
responseTime: response.headers.get('X-Response-Time-Ms'),
payloadSize: response.headers.get('X-Payload-Size-Bytes'),
co2: response.headers.get('X-Estimated-CO2-Grams'),
};
};
const fetchStations = async (): Promise<void> => {
try {
const res = await fetch(`${API_BASE_URL}/stations`);
captureEcoHeaders(res);
stations.value = await res.json();
} catch (err) {
console.error('Erreur chargement stations', err);
}
};
const startSession = async (userName: string, stationId: number, kwhRequested:
number): Promise<boolean> => {
errorTitle.value = '';
errorMessage.value = '';
try {
    const res = await fetch(`${API_BASE_URL}/sessions`, {   
method: 'POST',
headers: { 'Content-Type': 'application/json' },
body: JSON.stringify({ userName, stationId, kwhRequested }),
});
captureEcoHeaders(res);
if (!res.ok) {
const errorData: ApiProblemDetails = await res.json();
errorTitle.value = errorData.title || 'Erreur';
errorMessage.value = errorData.detail || 'Une erreur est survenue';
return false;
}

await fetchStations();
return true;
} catch (err) {
errorMessage.value = 'Impossible de contacter l\'API EcoPark.';
return false;
}
};
return {
stations,
errorTitle,
errorMessage,
ecoMetrics,
fetchStations,
startSession,
};
}