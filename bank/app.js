const users = [
        { username: "Rojvan Suleiman", password: "Roj",},
        { username: "Tariq Abbas", password: "Tabbs",},
        { username: "Ahmed Salih", password: "Amad",},
        { username: "Dylan Smit", password: "Dylan Dennis",},
        { username: "Furkan Kara", password: "Furkara",},
  ];
   
  const accounts = [
    { accountName: 'Betaalrekening', balance: 10000 },
    { accountName: 'Spaarrekening', balance: 50000 },
  ];
   
  const transactions = [
    { type: 'inkomend', date: '2024-11-01', amount: 15000.0 },
    { type: 'uitgaand', date: '2024-11-03', amount: -500.0 },
    { type: 'inkomend', date: '2024-11-05', amount: 1200.0 },
    { type: 'uitgaand', date: '2024-11-10', amount: -3260.0 },
  ];
   
  let isLoggedIn = false;
   
  document.querySelectorAll('nav a').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').substring(1);
        if (isLoggedIn || targetId === 'login') {
            showSection(targetId);
        } else {
            alert('Je moet eerst inloggen om toegang te krijgen tot deze sectie.');
        }
    });
  });
   
  function showSection(sectionId) {
    document.querySelectorAll('section').forEach(section => {
        section.style.display = 'none';
    });
    document.getElementById(sectionId).style.display = 'block';
  }
   
  document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const user = users.find(u => u.username === username && u.password === password);
    if (user) {
        isLoggedIn = true;
        showSection('rekeningen');
        displayAccounts();
        populateAccountDropdowns(); 
    } else {
        document.getElementById('errorMessage').style.display = 'block';
    }
  });
   
  function displayAccounts() {
    const accountList = document.getElementById('accountList');
    accountList.innerHTML = '';
    accounts.forEach(account => {
        const div = document.createElement('div');
        div.innerHTML = `<p>${account.accountName}: €${account.balance.toFixed(2)}</p>`;
        accountList.appendChild(div);
    });
  }
   
  function populateAccountDropdowns() {
    const fromAccountDropdown = document.getElementById('fromAccount');
    const toAccountDropdown = document.getElementById('toAccount');
    fromAccountDropdown.innerHTML = '';
    toAccountDropdown.innerHTML = '';
   
    accounts.forEach((account, index) => {
        const fromOption = document.createElement('option');
        fromOption.value = index;
        fromOption.textContent = account.accountName;
   
        const toOption = fromOption.cloneNode(true);
   
        fromAccountDropdown.appendChild(fromOption);
        toAccountDropdown.appendChild(toOption);
    });
  }
   
  document.getElementById('transferForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const fromAccountIndex = document.getElementById('fromAccount').value;
    const toAccountIndex = document.getElementById('toAccount').value;
    const amount = parseFloat(document.getElementById('amount').value);
   
    if (fromAccountIndex === toAccountIndex) {
        alert('Van en naar rekening kunnen niet hetzelfde zijn.');
        return;
    }
   
    if (amount <= 0) {
        alert('Bedrag moet groter zijn dan nul.');
        return;
    }
   
    if (accounts[fromAccountIndex].balance < amount) {
        alert('Onvoldoende saldo op de gekozen rekening.');
        return;
    }
   
    accounts[fromAccountIndex].balance -= amount;
    accounts[toAccountIndex].balance += amount;
   
    transactions.push({
        type: 'uitgaand',
        date: new Date().toISOString().split('T')[0],
        amount: -amount,
    });
   
    transactions.push({
        type: 'inkomend',
        date: new Date().toISOString().split('T')[0],
        amount: amount,
    });
   
    displayAccounts();
    document.getElementById('transferMessage').textContent = 'Overschrijving succesvol!';
    document.getElementById('transferMessage').style.display = 'block';
   
    setTimeout(() => {
        document.getElementById('transferMessage').style.display = 'none';
    }, 3000);
  });
   
  document.getElementById('newAccountBtn').addEventListener('click', function () {
    const accountName = prompt('Naam van de nieuwe rekening:');
    const balance = prompt('Beginsaldo:');
    if (accountName && balance) {
        accounts.push({ accountName, balance: parseFloat(balance) });
        displayAccounts();
        populateAccountDropdowns(); 
    }
  });
   
  document.getElementById('filterButton').addEventListener('click', function () {
    const filterType = document.getElementById('filterType').value;
    const filterDate = document.getElementById('filterDate').value;
   
    const filteredTransactions = transactions.filter(transaction => {
        return (
            (filterType === 'all' || transaction.type === filterType) &&
            (!filterDate || transaction.date === filterDate)
        );
    });
   
    displayTransactions(filteredTransactions);
  });
   
  function displayTransactions(transactionArray) {
    const transactionList = document.getElementById('transactionList');
    transactionList.innerHTML = '';
   
    transactionArray.forEach(transaction => {
        const transactionDiv = document.createElement('div');
        transactionDiv.innerHTML =
            `<p>Type: ${transaction.type}</p>
            <p>Datum: ${transaction.date}</p>
            <p>Bedrag: €${transaction.amount.toFixed(2)}</p>
            <hr>`;
        transactionList.appendChild(transactionDiv);
    });
  }
   
  let investmentBalance = 7600.0; 
  let currentPrice = 120.0; 
  let ownedUnits = 3; 
   
  
  document.getElementById('buyButton').addEventListener('click', () => {
    const selectedCategory = document.getElementById('investmentCategory').value;
    const selectedProduct = document.getElementById('investmentProduct').value;
    const amount = parseFloat(document.getElementById('investmentAmount').value);
    const totalCost = currentPrice * amount;
   
    if (!selectedProduct) {
      showInvestmentMessage('Selecteer een product.', 'red');
      return;
    }
   
    if (amount <= 0 || isNaN(amount)) {
      showInvestmentMessage('Voer een geldig aantal eenheden in.', 'red');
      return;
    }
   
    if (totalCost > investmentBalance) {
      showInvestmentMessage('Onvoldoende saldo voor deze aankoop.', 'red');
      return;
    }
   
    
    ownedUnits += amount;
    investmentBalance -= totalCost;
   
    updateInvestmentDisplay();
    showInvestmentMessage(`Je hebt €${totalCost.toFixed(2)} geïnvesteerd in ${selectedProduct}.`, 'green');
  });
   
  
  document.getElementById('sellButton').addEventListener('click', () => {
    const selectedCategory = document.getElementById('investmentCategory').value;
    const selectedProduct = document.getElementById('investmentProduct').value;
    const amount = parseFloat(document.getElementById('investmentAmount').value);
    const totalEarnings = currentPrice * amount;
   
    if (!selectedProduct) {
      showInvestmentMessage('Selecteer een product om te verkopen.', 'red');
      return;
    }
   
    if (amount <= 0 || isNaN(amount)) {
      showInvestmentMessage('Voer een geldig aantal eenheden in.', 'red');
      return;
    }
   
    if (amount > ownedUnits) {
      showInvestmentMessage('Je hebt niet genoeg eenheden om te verkopen.', 'red');
      return;
    }
   
    
    ownedUnits -= amount;
    investmentBalance += totalEarnings;
   
    updateInvestmentDisplay();
    showInvestmentMessage(`Je hebt €${totalEarnings.toFixed(2)} verdiend met de verkoop van ${selectedProduct}.`, 'green');
  });
   
  
  function updateInvestmentDisplay() {
    document.getElementById('availableBalance').textContent = `€${investmentBalance.toFixed(2)}`;
    document.getElementById('currentPrice').textContent = `€${currentPrice.toFixed(2)}`;
    document.getElementById('ownedUnits').textContent = ownedUnits;
  }
   
  function showInvestmentMessage(message, color) {
    const messageElement = document.getElementById('investmentMessage');
    messageElement.textContent = message;
    messageElement.style.color = color;
  }
   
  
  const investmentCategories = {
    aandelen: [
      { value: 'techCorp', label: 'TechCorp' },
      { value: 'finCorp', label: 'FinCorp' },
    ],
    crypto: [
      { value: 'bitcoin', label: 'Bitcoin' },
      { value: 'ethereum', label: 'Ethereum' },
    ],
  };
   
  document.getElementById('investmentCategory').addEventListener('change', (e) => {
    const category = e.target.value;
    const productSelect = document.getElementById('investmentProduct');
    productSelect.innerHTML = '';
   
    investmentCategories[category].forEach(product => {
      const option = document.createElement('option');
      option.value = product.value;
      option.textContent = product.label;
      productSelect.appendChild(option);
    });
  });
   
  
  setInterval(() => {
    const priceChange = (Math.random() - 0.5) * 10; 
    currentPrice = Math.max(10, currentPrice + priceChange); 
    document.getElementById('currentPrice').textContent = `€${currentPrice.toFixed(2)}`;
  }, 5000);
   
  
  document.getElementById('investmentCategory').dispatchEvent(new Event('change'));
  let cryptoBalansen = {
    bitcoin: 0,
    ethereum: 0,
    litecoin: 0,
};

const cryptoPrijzen = {
    bitcoin: 90000,
    ethereum: 4800,
    litecoin: 250,
};

document.getElementById('cryptoKopen').addEventListener('click', () => {
  const type = document.getElementById('cryptoType').value;
  const bedrag = parseFloat(document.getElementById('cryptoBedrag').value);

  if (bedrag > 60000) {
      toonCryptoBericht('Te hoog bedrag: maximaal €60.000 toegestaan.', 'red');
      return;
  }

  if (bedrag > 0) {
      cryptoBalansen[type] += bedrag / cryptoPrijzen[type];
      updateCryptoBalansen();
      toonCryptoBericht(`Je hebt €${bedrag.toFixed(2)} geïnvesteerd in ${type}.`, 'green');
  } else {
      toonCryptoBericht('Voer een geldig bedrag in.', 'red');
  }
});

function toonCryptoBericht(bericht, kleur) {
  const berichtElement = document.getElementById('cryptoBericht');
  berichtElement.textContent = bericht;
  berichtElement.style.color = kleur;
}


document.getElementById('cryptoVerkopen').addEventListener('click', () => {
    const type = document.getElementById('cryptoType').value;
    const bedrag = parseFloat(document.getElementById('cryptoBedrag').value);

    if (bedrag > 0 && bedrag / cryptoPrijzen[type] <= cryptoBalansen[type]) {
        cryptoBalansen[type] -= bedrag / cryptoPrijzen[type];
        updateCryptoBalansen();
        toonCryptoBericht(`Je hebt €${bedrag.toFixed(2)} van ${type} verkocht.`, 'green');
    } else {
        toonCryptoBericht('Onvoldoende balans of ongeldig bedrag.', 'red');
    }
});

function updateCryptoBalansen() {
    document.getElementById('btcBalans').textContent = (cryptoBalansen.bitcoin * cryptoPrijzen.bitcoin).toFixed(2);
    document.getElementById('ethBalans').textContent = (cryptoBalansen.ethereum * cryptoPrijzen.ethereum).toFixed(2);
    document.getElementById('ltcBalans').textContent = (cryptoBalansen.litecoin * cryptoPrijzen.litecoin).toFixed(2);
}

function toonCryptoBericht(bericht, kleur) {
    const berichtElement = document.getElementById('cryptoBericht');
    berichtElement.textContent = bericht;
    berichtElement.style.color = kleur;
}


setInterval(() => {
    for (const type in cryptoPrijzen) {
        const wijziging = (Math.random() - 0.5) * 0.1 * cryptoPrijzen[type];
        cryptoPrijzen[type] = Math.max(1, cryptoPrijzen[type] + wijziging);
    }
    updateCryptoBalansen();
}, 5000);
