import { Component, OnInit } from '@angular/core';
import { Router, NavigationEnd, RouterOutlet, RouterLink, RouterLinkActive } from '@angular/router';
import { filter } from 'rxjs/operators';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, RouterLink, RouterLinkActive, CommonModule],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent implements OnInit {

  onMainPage: boolean = true;
  router_link: string = 'hello';
  constructor(private router: Router){
    //checks for navigation end- when the navigation to a new page has finished- to see what the route is
    this.router.events
      .pipe(filter(event => event instanceof NavigationEnd))
      .subscribe((event: NavigationEnd) => {
        this.onMainPage = event.url === '/';
      });
  }

  ngOnInit() {
    this.onMainPage = this.router.url === '/';
  }

}