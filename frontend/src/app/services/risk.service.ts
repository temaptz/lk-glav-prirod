import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface RiskDto {
  id: number;
  koap_article: string;
  min_fine: number;
  max_fine: number;
  description: string;
}

@Injectable({ providedIn: 'root' })
export class RiskService {
  private http = inject(HttpClient);

  list(): Observable<RiskDto[]> {
    return this.http.get<RiskDto[]>('/api/risk');
  }
}
