import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { firstValueFrom } from 'rxjs';
@Component({
  selector: 'app-home',
  imports: [FormsModule, CommonModule],
  templateUrl: './home.component.html',
  styleUrl: './home.component.css'
})
export class HomeComponent {

  constructor(private http: HttpClient) {
    if (!this.databaseSet){
      this.http.get('http://localhost:8080/credit-system/public/backend.php')
      .subscribe();
      this.databaseSet = true;
    }
  }

  title = 'credit-system';
  databaseSet: boolean = false;
  userSet: boolean = false;
  user: string = "";
  pass: string = "";
  username_input: string = "";
  password_input: string = "";
  item_input: string = "";
  price_input: number = 0;
  errorMessage: string = "";

  items: {name: string, price: number, used: boolean}[] = [];


  insertUser(): void {
    this.errorMessage = "";
    this.user = this.username_input.trim().toLowerCase();
    this.pass = this.password_input.trim().toLowerCase();
    const postData = {
      name: this.user,
      password: this.pass
    };
    console.log(postData);
    this.http.post<{result:string, message:string}>('http://localhost:8080/credit-system/public/backend.php?action=insertUser', postData, {
      headers: {
        'Content-Type': 'application/json'
      }
    })
    .subscribe({
      next: (data) => {
        console.log('Insert Response:', data);
        if (data.result !== 'error'){
          this.userSet = true;
          this.items = []; 
          this.getAllitems();
        }
        else{
          this.errorMessage = data.message;
        }
      },
      error: (error) => {
        console.error('Error:', error);
        this.errorMessage = error.message;
      }
    });
  }

  insertitem(): void {
    this.errorMessage = "";
    const item= this.item_input.trim().toLowerCase();
    const price= this.price_input;
    if (!/^\d+(\.\d{1,2})?$/.test(price.toString())) {
      this.errorMessage = 'Price must have at most 2 decimal places.';
      return;
    }
    const postData = {
      name: item,
      price: price
    };
    const jsonData = JSON.stringify(postData);
    this.errorMessage = "";
    console.log(jsonData);
    this.http.post('http://localhost:8080/credit-system/public/item_logic.php?action=insertitem', jsonData, {
      headers: {
        'Content-Type': 'application/json'
      }
    })
    .subscribe({
      next: (data) => {
        console.log('Insert Response:', data);
        this.item_input = "";
        this.price_input= 0;
        this.getAllitems();
      },
      error: (error) => {
        console.error('Error inserting item:', error);
        this.errorMessage = error.message;
      }
    });
  }

  addUsertoitem(name: string): void{
    this.errorMessage = "";
    const postData= {
      item_name : name,
      user : this.user
    };
    this.http.post('http://localhost:8080/credit-system/public/item_logic.php?action=addUserToitem',postData,{
      headers: {
        'Content-Type': 'application/json'
      }
    })
    .subscribe({
      next: (data) => {
        console.log('Add to user item Response:', data);
        const item = this.items.find(f => f.name === name);
      if (item) {
        item.used = true;
      }
      },
      error: (error) => {
        console.error('Error adding user to item:', error);
      }
    });
}

deleteUserfromitem(name: string): void{
  this.errorMessage = "";
  const postData= {
    item_name : name,
    user : this.user
  };
  this.http.post('http://localhost:8080/credit-system/public/item_logic.php?action=deleteUserfromitem',postData,{
    headers: {
      'Content-Type': 'application/json'
    }
  })
  .subscribe({
    next: (data) => {
      console.log('Delete user from item Response:', data);
      const item = this.items.find(f => f.name === name);
      if (item) {
        item.used = false;
      }
    },
    error: (error) => {
      console.error('Error deleting user from item:', error);
    }
  });
}

  getAllitems(): void{
    this.errorMessage = "";
    this.http.get<{data: Array<{name: string, price:number}>, result:String, message:string}>('http://localhost:8080/credit-system/public/backend.php?action=getAllitems')
      .subscribe({
        next: async (response) => {
          console.log('Select Response:', response);
          if (response.result!= 'error'){
              for (let i = 0; i < response.data.length; i++){
                let name = response.data[i].name;
                if (this.items.find(f=> f.name===name) === undefined){
                  let price = response.data[i].price;
                  let used = (await this.checkUseranditem(name)).valueOf();
                  this.items.push({name, price, used});
                }
              }
        }
        else{
          this.errorMessage = response.message;
        }
      },
        error: (error) => {
          console.error('Error selecting from database:', error);
        }
      });
  }

  async checkUseranditem(item_name: string): Promise<boolean> {
    const postData = {
      user_name: this.user,
      item_name: item_name
    };
  
    try {
      const response = await firstValueFrom(
        this.http.post<{ found: string }>(
          'http://localhost:8080/credit-system/public/backend.php?action=checkUseranditem',
          postData,
          { headers: { 'Content-Type': 'application/json' } }
        )
      );
      console.log('Check user and item Response:', response);
      return response.found === 'true';
    } catch (error) {
      console.error('Error checking user and item:', error);
      return false;
    }
  }
}
