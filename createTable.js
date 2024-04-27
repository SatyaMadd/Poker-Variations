function createTable(values, nameUse){
  let messages = values.messages;
  let globalVs = values.globalVar;
  let betTrackAr = values.betTrackAr;
  let commCards = values.cards;
  let oldTable = document.getElementById('myTable');
  if (oldTable) oldTable.remove();
  let oldCommCardsTable = document.getElementById('commCardsTable');
  if (oldCommCardsTable) oldCommCardsTable.remove();
  let commCardsTable = document.createElement('table');
  commCardsTable.id = 'commCardsTable';
  let everyoneIsReady = messages.every(msg => msg.ready === 'yes');
  let headers = ['Player#', 'Name', 'User ID'];
  let checkShow = false;
  let readyButton = document.getElementById("readyB");
  let roundTxt = document.getElementById("roundN");
  let potTxt = document.getElementById("potAm");
  if (everyoneIsReady) {
    headers.push('Card #1', 'Suit #1', 'Card #2', 'Suit #2', 'Chips', 'Turn');
    readyButton.style.display = "none";
    commCardsTable.style.display = "block";
    roundTxt.innerHTML = "Round: " + globalVs[0].round;
    roundTxt.style.display = "block";
    potTxt.innerHTML = "Pot: " + globalVs[0].pot;
    potTxt.style.display = "block";
  } else {
    headers.push('Ready');
    commCardsTable.style.display = "none";
    readyButton.style.display = "block";
    roundTxt.style.display = "none";
    potTxt.style.display = "none";
  }
  let table = document.createElement('table');
  table.id = 'myTable'; 
  let thead = document.createElement('thead');
  let headerRow = document.createElement('tr');
  headers.forEach(header => {
    let th = document.createElement('th');
    th.textContent = header;
    headerRow.appendChild(th);
  });
  thead.appendChild(headerRow);
  table.appendChild(thead);
  let tbody = document.createElement('tbody');
  messages.slice(0, 6).forEach((msg, index) => {
    let row = document.createElement('tr');
    let orderCell = document.createElement('td');
    orderCell.textContent = index + 1; 
    row.appendChild(orderCell);
    ['name', 'userID'].forEach(key => {
      let td = document.createElement('td');
      td.textContent = msg[key];
      row.appendChild(td);
    });
    if (everyoneIsReady) {
      ['cardNumber1', 'suit1', 'cardNumber2', 'suit2'].forEach(key => {
        let td = document.createElement('td');
        td.textContent = (nameUse == msg.name) ? msg[key] : 'Hidden';
        row.appendChild(td);
      });
      let td = document.createElement('td');
      td.textContent = msg.chips;
      row.appendChild(td);
      td = document.createElement('td');
      td.textContent = msg.turn;
      if((nameUse == msg.name)&&(msg.turn == 'yes')){
        checkShow = true;
      }
      row.appendChild(td);
    } else {
      let td = document.createElement('td');
      td.textContent = msg.ready;
      row.appendChild(td);
    }
    tbody.appendChild(row);
  });
  table.appendChild(tbody);
  let commCardsHeaders = ['Card#', 'Value', 'Suit'];
  let commCardsThead = document.createElement('thead');
  let commCardsHeaderRow = document.createElement('tr');
  commCardsHeaders.forEach(header => {
    let th = document.createElement('th');
    th.textContent = header;
    commCardsHeaderRow.appendChild(th);
  });
  commCardsThead.appendChild(commCardsHeaderRow);
  commCardsTable.appendChild(commCardsThead);
  let commCardsTbody = document.createElement('tbody');
  commCards.forEach(card => {
    let row = document.createElement('tr');
    ['id', 'cardNumber', 'suit'].forEach(key => {
      let td = document.createElement('td');
      td.textContent = card[key];
      row.appendChild(td);
    });
    commCardsTbody.appendChild(row);
  });
  commCardsTable.appendChild(commCardsTbody);  
  let checkButton = document.getElementById("checkB");
  let betButton = document.getElementById("betB");
  let betText = document.getElementById("betAm");
  let foldButton = document.getElementById("foldB");
  if(checkShow){
    if(betTrackAr[betTrackAr.length-1].amount != 0){
      checkButton.textContent = "Call";
    }else{
      checkButton.textContent = "Check"
    }
    checkButton.style.display = "block";
    betButton.style.display = "block";
    betText.style.display = "block";
    foldButton.style.display = "block";
    
  }else{
    checkButton.style.display = "none";
    betButton.style.display = "none";
    betText.style.display = "none";
    foldButton.style.display = "none";
  }
  document.getElementById('nameShow').insertAdjacentElement("afterend", table);
table.insertAdjacentElement("afterend", commCardsTable);
}