export interface Station {
id: number;
locationName: string;
powerKw: number;
isAvailable: boolean;
_links?: {
self: { href: string; method: string };
};
}
export interface ApiProblemDetails {
type: string;
title: string;
status: number;
detail: string;
}
export interface EcoMetrics {
responseTime: string | null;
payloadSize: string | null;
co2: string | null;
}