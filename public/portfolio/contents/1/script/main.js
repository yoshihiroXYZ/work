'use strict'; 
import {questions} from './questionData.js';

// --- DOM取得 ---
const entered = document.getElementById('entered');
const caret = document.getElementById('caret');
const remained = document.getElementById('remained');
const inputText = document.getElementById('inputText');
const game = document.getElementById('game');
const message = document.getElementById('message');
const replayBtn = document.getElementById('replayBtn');

// --- 状態 ---
let remainedTextWords = [];
let enteredTextWords = [];
let currentKey;
let currentText;

// --- ユーティリティ ---
const isControlKey = (e) => (
  e.ctrlKey || e.metaKey || e.altKey ||
  ['Shift','Alt','Control','Meta','CapsLock','Tab','Escape','ArrowLeft','ArrowRight','ArrowUp','ArrowDown'].includes(e.key)
);
const isSingleVisibleChar = (key) => key.length === 1;
const CASE_SENSITIVE = true;

// --- 描画 ---
const render = () => {
  const nextChar = remainedTextWords[0] ?? '';
  const rest = remainedTextWords.slice(1).join('');
  entered.textContent = enteredTextWords.join('');
  caret.textContent = nextChar;            // 次に打つ1文字に点滅下線
  remained.textContent = rest;           // それ以降の残り
};

// --- 問題セット ---
const setQuestion = () => {
  currentKey = Math.floor(Math.random() * questions.length);
  currentText = questions[currentKey];
  questions.splice(currentKey, 1);

  inputText.value = '';
  enteredTextWords = [];
  remainedTextWords = currentText.split('');

  render();
};

// --- 初期化 ---
setQuestion();
inputText.focus();

// --- 入力系イベント（IME・貼り付け禁止・フォーカス維持） ---
let isComposing = false;
inputText.addEventListener('compositionstart', () => (isComposing = true));
inputText.addEventListener('compositionend', () => (isComposing = false));
inputText.addEventListener('paste', e => e.preventDefault());
inputText.addEventListener('drop', e => e.preventDefault());
inputText.addEventListener('contextmenu', e => e.preventDefault());
window.addEventListener('blur', () => setTimeout(() => inputText.focus(), 0));

// --- キー入力（正解以外は受け付けない） ---
document.addEventListener('keydown', (e) => {
  //  Ctrl+Enter / Cmd+Enter で「もう一度プレイする」実行（クリア画面表示中のみ）
  if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
      if (message && !message.classList.contains('hidden')) {
      e.preventDefault();
      replayBtn.click();
      }
      return; 
  }
  if (
    e.key === 'F12' ||
    (e.key === 'I' && (e.ctrlKey || e.metaKey) && e.shiftKey) || // DevTools
    (e.key === 'J' && (e.ctrlKey || e.metaKey) && e.shiftKey) || // Console
    (e.key === 'C' && (e.ctrlKey || e.metaKey) && e.shiftKey) || // Inspect
    e.key === 'F5' ||                                            // Reload
    (e.key === 'R' && (e.ctrlKey || e.metaKey))                  // Reload
  ) {
    return;
  }

  if (message && !message.classList.contains('hidden')) return; // 終了後は無視

  if (isComposing || isControlKey(e)) return;

  if (e.key === 'Enter') { e.preventDefault(); return; } // Enter単体は無効化
  if (document.activeElement !== inputText) inputText.focus();
  if (!isSingleVisibleChar(e.key)) { e.preventDefault(); return; }

  let expected = remainedTextWords[0];
  let pressed  = e.key;
  if (!CASE_SENSITIVE) { expected = expected?.toLowerCase?.(); pressed = pressed.toLowerCase(); }

  if (pressed !== expected) {
      e.preventDefault();
      remained.classList.add('remained-error');
      setTimeout(() => remained.classList.remove('remained-error'), 150);
      return;
  }

  e.preventDefault();
  enteredTextWords.push(remainedTextWords.shift());
  render();

  if (remainedTextWords.length === 0) {
      if (questions.length === 0) {
      game.classList.add('hidden');
      message.classList.remove('hidden');
      } else {
      setQuestion();
      }
  }
  
  replayBtn.addEventListener('click', () => {
      window.location.reload();
  });
});
