import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { AuthService } from '../auth.service';

@Component({
  selector: 'app-signup',
  imports: [FormsModule, CommonModule],
  templateUrl: './signup.component.html',
  styleUrl: './signup.component.css'
})
export class SignupComponent {

  username_input: string = "";
  password_input: string = "";
  errorMessage: string = "";

  constructor(private http: HttpClient, private router: Router, private auth: AuthService) {}

  signUp(): void {
    this.errorMessage = "";
    const user = this.username_input.trim().toLowerCase();
    const pass = this.password_input.trim().toLowerCase();
    const postData = { name: user, password: pass };

    this.http.post<{ result: string, message: string }>(
      'http://localhost:8080/credit-system/public/backend.php?action=signUp',
      postData,
      { headers: { 'Content-Type': 'application/json' } }
    ).subscribe({
      next: (data) => {
        if (data.result !== 'error') {
          this.auth.login(user);
          this.router.navigate(['/home']);
        } else {
          this.errorMessage = data.message;
        }
      },
      error: (error) => {
        this.errorMessage = error.message;
      }
    });
  }
}
