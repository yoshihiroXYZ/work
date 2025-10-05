const grid = document.querySelector("#grid");
document.querySelector("#year").textContent = new Date().getFullYear();

let items = [];

async function init() {
  const res = await fetch("./data/projects.json", { cache: "no-store" });
  items = await res.json();
  grid.innerHTML = items.map(cardHTML).join("");
}

function cardHTML(i, idx) {
  const index = typeof idx === "number" ? idx : items.indexOf(i);
  const alt = `${i.title} のサムネイル`;
  const tags = (i.tags||[]).map(t => `<span class="tag">${t}</span>`).join("");
  return `
    <article class="card">
      <a href="${i.url}" target="_blank" rel="noopener noreferrer">
        <img class="thumb" src="${i.thumb}" alt="${alt}" loading="lazy" />
      </a>
      <div class="card-body">
        <h2 class="title"><a href="${i.url}" target="_blank" rel="noopener noreferrer">${i.title}</a></h2>
        <p class="desc">${i.description||""}</p>
        <div class="tags">${tags}</div>
        <div class="actions">

          <a class="btn secondary" href="${i.url}" target="_blank" rel="noopener noreferrer">作品を開く</a>
        </div>
      </div>
    </article>`;
}

init();
