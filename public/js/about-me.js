

function organizeCards() {
    const content = document.getElementById('about-me-content');
    if (!content) return;

    const cards = Array.from(content.querySelectorAll('.about-me-row-card'));
    if (cards.length === 0) return;

    const existingRows = content.querySelectorAll('.about-me-row');
    existingRows.forEach(row => row.remove());

    cards.forEach(card => {
        if (card.parentNode) {
            card.parentNode.removeChild(card);
        }
    });

    if (window.innerWidth > 800) {
        const row1 = document.createElement('article');
        row1.className = 'about-me-row';

        const row2 = document.createElement('article');
        row2.className = 'about-me-row';

        for (let i = 0; i < 3 && i < cards.length; i++) {
            row1.appendChild(cards[i]);
        }

        for (let i = 3; i < cards.length; i++) {
            row2.appendChild(cards[i]);
        }

        content.appendChild(row1);
        content.appendChild(row2);
    } else {
        const row = document.createElement('article');
        row.className = 'about-me-row';

        cards.forEach(card => {
            row.appendChild(card);
        });

        content.appendChild(row);
    }
}

function organizeStatsCards() {
    const container = document.getElementById('stats-content');
    if (!container) {
        return;
    }

    const cards = Array.from(container.querySelectorAll('.stats-content-row-card'));
    if (cards.length === 0) {
        return;
    }

    const existingRows = container.querySelectorAll('.stats-content-row');
    existingRows.forEach(row => row.remove());

    cards.forEach(card => {
        if (card.parentNode) {
            card.parentNode.removeChild(card);
        }
    });

    const windowWidth = window.innerWidth;

    let rowPattern;
    if (windowWidth >= 1500) {
        rowPattern = [4, 3];
    } else if (windowWidth >= 900) {
        rowPattern = [3, 2];
    } else if (windowWidth >= 700) {
        rowPattern = [2, 1];
    } else {
        rowPattern = [1];
    }

    let cardIndex = 0;
    let rowIndex = 0;

    while (cardIndex < cards.length) {
        const cardsInRow = rowPattern[rowIndex % rowPattern.length];

        const row = document.createElement('div');
        row.className = 'stats-content-row';

        for (let j = 0; j < cardsInRow && cardIndex < cards.length; j++) {
            row.appendChild(cards[cardIndex]);
            cardIndex++;
        }

        container.appendChild(row);

        rowIndex++;
    }

}

organizeStatsCards();
organizeCards()
window.addEventListener('resize', organizeStatsCards);
window.addEventListener('resize', organizeCards);
