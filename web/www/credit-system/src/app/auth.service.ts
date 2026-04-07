import { Injectable } from '@angular/core';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly KEY = 'isLoggedIn';
  private readonly USER_KEY = 'username';
  loginError: string = '';

  get isLoggedIn(): boolean {
    return localStorage.getItem(this.KEY) === 'true';
  }

  get username(): string {
    return localStorage.getItem(this.USER_KEY) ?? '';
  }

  login(username: string): void {
    localStorage.setItem(this.KEY, 'true');
    localStorage.setItem(this.USER_KEY, username);
  }

  logout(): void {
    localStorage.removeItem(this.KEY);
    localStorage.removeItem(this.USER_KEY);
  }
}
