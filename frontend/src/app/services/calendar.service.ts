import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface CalendarEventDto {
  id: number;
  title: string;
  event_date: string;
  requirement_id: number | null;
}

@Injectable({ providedIn: 'root' })
export class CalendarService {
  private http = inject(HttpClient);

  list(orgId: number): Observable<CalendarEventDto[]> {
    return this.http.get<CalendarEventDto[]>(`/api/calendar?orgId=${orgId}`);
  }
}
