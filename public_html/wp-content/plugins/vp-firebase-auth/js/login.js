import { initializeApp } from 'firebase/app';
import {
  getAuth, signOut, signInWithPopup, signInWithCredential,
  GoogleAuthProvider, FacebookAuthProvider,
  RecaptchaVerifier, signInWithPhoneNumber,
  signInWithEmailAndPassword, createUserWithEmailAndPassword, sendEmailVerification,
  sendPasswordResetEmail, sendSignInLinkToEmail, isSignInWithEmailLink, signInWithEmailLink,
} from 'firebase/auth';

const cfg = window.VPAuth || {};
const mounts = document.querySelectorAll('[data-vp-auth]');
if (mounts.length && cfg.firebase) {
  const app = initializeApp(cfg.firebase);
  const auth = getAuth(app);
  auth.languageCode = 'en';
  mounts.forEach(mount);

  function mount(root) {
    const redirect = root.dataset.redirect || cfg.account || '/';
    const isSignup = (root.dataset.mode || cfg.mode || 'signin') === 'signup';
    let busy = false;
    let confirmation = null;
    let recaptcha = null;

    const el = (tag, attrs = {}, ...kids) => {
      const n = document.createElement(tag);
      Object.entries(attrs).forEach(([k, v]) => {
        if (k === 'class') n.className = v;
        else if (k.startsWith('on')) n.addEventListener(k.slice(2), v);
        else if (v === false || v == null) { /* skip */ }
        else n.setAttribute(k, v === true ? '' : v);
      });
      kids.forEach((c) => n.append(c));
      return n;
    };
    const icon = (name) => {
      const n = document.createElement('span');
      n.className = 'vp-auth__icon is-' + name;
      n.setAttribute('aria-hidden', 'true');
      n.innerHTML = {
        google: '<svg viewBox="0 0 24 24" focusable="false"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>',
        facebook: '<svg viewBox="0 0 24 24" focusable="false"><path fill="currentColor" d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.875v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>',
        email: '<svg viewBox="0 0 24 24" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" d="M3.5 6.5A1.5 1.5 0 0 1 5 5h14a1.5 1.5 0 0 1 1.5 1.5v11A1.5 1.5 0 0 1 19 19H5a1.5 1.5 0 0 1-1.5-1.5v-11z"/><path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M4.2 7.2 12 13l7.8-5.8"/></svg>',
      }[name] || '';
      return n;
    };

    const status = el('p', { class: 'vp-auth__status', role: 'status', hidden: '' });
    const say = (text, isError) => {
      status.textContent = text || '';
      status.hidden = !text;
      status.classList.toggle('is-error', !!isError);
    };

    const friendly = (e) => {
      const map = {
        'auth/invalid-phone-number': 'Enter a valid 10-digit mobile number.',
        'auth/invalid-verification-code': 'That code is not correct. Enter only the 6 digits from the latest SMS.',
        'auth/code-expired': 'That code expired. Please request a new one.',
        'auth/too-many-requests': 'Too many attempts. Please wait a few minutes and try again.',
        'auth/invalid-credential': 'Email or password is not correct.',
        'auth/wrong-password': 'Email or password is not correct.',
        'auth/user-not-found': 'Email or password is not correct.',
        'auth/email-already-in-use': 'This email already has an account. Use Sign in, or reset your password.',
        'auth/weak-password': 'Password must be at least 6 characters.',
        'auth/invalid-email': 'Enter a valid email address.',
        'auth/popup-closed-by-user': 'Sign-in was cancelled.',
        'auth/cancelled-popup-request': 'Sign-in was cancelled.',
        'auth/popup-blocked': 'Your browser blocked the sign-in window. Please allow pop-ups and try again.',
        'auth/account-exists-with-different-credential': 'This email is already registered with another sign-in method. Use that one.',
        'auth/network-request-failed': 'Network problem. Check your connection and try again.',
        'auth/quota-exceeded': 'SMS limit reached. Please try email or Google instead.',
        'auth/missing-initial-state': 'Facebook sign-in was interrupted. Please try again and keep this tab open.',
        'auth/web-storage-unsupported': 'This browser is blocking sign-in storage. Allow cookies for this site and try again.',
      };
      const msg = (e && e.message) || '';
      if (/missing initial state/i.test(msg)) return map['auth/missing-initial-state'];
      return map[e && e.code] || 'Something went wrong. Please try again.';
    };

    async function run(fn) {
      if (busy) return;
      busy = true;
      root.classList.add('is-busy');
      say('');
      try { await fn(); } catch (e) { say(friendly(e), true); } finally { busy = false; root.classList.remove('is-busy'); }
    }

    async function bridge(user) {
      say('Signing you in…');
      const token = await user.getIdToken(true);
      const res = await fetch(cfg.session, {
        method: 'POST', credentials: 'same-origin',
        headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ redirect, idToken: token }),
      });
      const data = await res.json().catch(() => ({}));
      await signOut(auth).catch(() => { });
      if (!res.ok || !data.ok) {
        const err = new Error(data.message || 'We could not sign you in. Please try again or contact our team.');
        err.plain = true;
        throw err;
      }
      window.location.assign(data.redirect || redirect);
    }
    const finish = async (cred) => {
      try { await bridge(cred.user); } catch (e) { say(e.plain ? e.message : friendly(e), true); busy = false; root.classList.remove('is-busy'); throw null; }
    };
    const guard = (fn) => () => run(async () => { try { await fn(); } catch (e) { if (e === null) return; throw e; } });

    function waitForFb(timeout = 8000) {
      if (window.FB && window.FB.login) return Promise.resolve(window.FB);
      return new Promise((resolve, reject) => {
        const t0 = Date.now();
        const tick = () => {
          if (window.FB && window.FB.login) return resolve(window.FB);
          if (Date.now() - t0 > timeout) return reject({ code: 'auth/network-request-failed' });
          setTimeout(tick, 50);
        };
        tick();
      });
    }
    function fbLogin() {
      return new Promise((resolve, reject) => {
        waitForFb().then((FB) => {
          FB.login((res) => {
            if (res && res.authResponse && res.authResponse.accessToken) resolve(res.authResponse.accessToken);
            else reject({ code: 'auth/popup-closed-by-user' });
          }, { scope: 'email,public_profile' });
        }).catch(reject);
      });
    }
    async function facebookSignIn() {
      const token = await fbLogin();
      return signInWithCredential(auth, FacebookAuthProvider.credential(token));
    }
    const googleBtn = el('button', {
      type: 'button', class: 'vp-auth__btn vp-auth__social is-google',
      onclick: guard(async () => finish(await signInWithPopup(auth, new GoogleAuthProvider()))),
    }, icon('google'), el('span', {}, (isSignup ? 'Sign up with ' : 'Continue with ') + 'Google'));
    const facebookBtn = el('button', {
      type: 'button', class: 'vp-auth__btn vp-auth__social is-facebook',
      onclick: guard(async () => finish(await facebookSignIn())),
    }, icon('facebook'), el('span', {}, (isSignup ? 'Sign up with ' : 'Continue with ') + 'Facebook'));

    const digitsOnly = (s) => {
      const map = { '०': '0', '१': '1', '२': '2', '३': '3', '४': '4', '५': '5', '६': '6', '७': '7', '८': '8', '९': '9' };
      return String(s || '').replace(/[०-९]/g, (ch) => map[ch] || ch).replace(/\D/g, '');
    };
    const extractOtp = (s) => {
      const mapped = String(s || '').replace(/[०-९]/g, (ch) => ({ '०': '0', '१': '1', '२': '2', '३': '3', '४': '4', '५': '5', '६': '6', '७': '7', '८': '8', '९': '9' }[ch] || ch));
      const groups = mapped.match(/\d{6}/g) || [];
      const notPhone = groups.filter((g) => !/^91/.test(g));
      if (notPhone.length) return notPhone[notPhone.length - 1];
      if (groups.length) return groups[groups.length - 1];
      const all = digitsOnly(mapped);
      return all.length > 6 ? all.slice(-6) : all;
    };
    const phone = el('input', { type: 'tel', inputmode: 'numeric', autocomplete: 'tel-national', maxlength: '10', placeholder: '10-digit mobile number', 'aria-label': 'Mobile number' });
    const otp = el('input', { type: 'text', inputmode: 'numeric', autocomplete: 'one-time-code', maxlength: '32', placeholder: '6-digit code', 'aria-label': 'OTP code' });
    const sendBtn = el('button', { type: 'button', class: 'vp-auth__btn vp-auth__primary', onclick: guard(sendOtp) }, 'Send OTP');
    const verifyBtn = el('button', { type: 'button', class: 'vp-auth__btn vp-auth__primary', onclick: guard(verifyOtp) }, 'Verify & continue');
    const recaptchaBox = el('div', { class: 'vp-auth__recaptcha' });
    const otpStep = el('div', { class: 'vp-auth__step', hidden: '' }, otp, verifyBtn,
      el('button', { type: 'button', class: 'vp-auth__link', onclick: () => { otpStep.hidden = true; phoneStep.hidden = false; confirmation = null; say(''); } }, 'Change number'));
    const phoneStep = el('div', { class: 'vp-auth__step' },
      el('div', { class: 'vp-auth__phone' }, el('span', { class: 'vp-auth__cc' }, '+91'), phone), sendBtn);

    async function sendOtp() {
      const digits = digitsOnly(phone.value);
      if (!/^[6-9]\d{9}$/.test(digits)) throw { code: 'auth/invalid-phone-number' };
      phone.value = digits;
      if (!recaptcha) recaptcha = new RecaptchaVerifier(auth, recaptchaBox, { size: 'invisible' });
      try {
        confirmation = await signInWithPhoneNumber(auth, '+91' + digits, recaptcha);
      } catch (e) { try { recaptcha.clear(); } catch (_) { } recaptcha = null; recaptchaBox.innerHTML = ''; throw e; }
      phoneStep.hidden = true; otpStep.hidden = false; otp.value = ''; otp.focus();
      say('OTP sent to +91 ' + digits + '. Enter the 6-digit code.');
    }
    async function verifyOtp() {
      if (!confirmation) throw { code: 'auth/code-expired' };
      const code = extractOtp(otp.value);
      otp.value = code;
      if (code.length !== 6) throw { code: 'auth/invalid-verification-code' };
      await finish(await confirmation.confirm(code));
    }
    otp.addEventListener('input', () => {
      const code = extractOtp(otp.value);
      if (otp.value !== code) otp.value = code;
      if (code.length === 6 && !busy) verifyBtn.click();
    });
    otp.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); verifyBtn.click(); }
    });
    const phonePane = el('div', { class: 'vp-auth__pane', 'data-pane': 'phone', hidden: isSignup ? '' : false }, phoneStep, otpStep, recaptchaBox);

    const email = el('input', { type: 'email', autocomplete: 'email', placeholder: 'Email address', 'aria-label': 'Email' });
    const pass = el('input', { type: 'password', autocomplete: isSignup ? 'new-password' : 'current-password', placeholder: isSignup ? 'Create a password (min 6 characters)' : 'Password', 'aria-label': 'Password' });
    const needEmail = () => { const v = email.value.trim(); if (!v) throw { code: 'auth/invalid-email' }; return v; };

    const signInBtn = el('button', {
      type: 'button', class: 'vp-auth__btn ' + (isSignup ? 'vp-auth__secondary' : 'vp-auth__primary'), onclick: guard(async () => {
        const cred = await signInWithEmailAndPassword(auth, needEmail(), pass.value);
        if (!cred.user.emailVerified) {
          await sendEmailVerification(cred.user).catch(() => { });
          await signOut(auth);
          say('Please verify your email first. We just sent you a verification link — open it, then sign in again.', true);
          throw null;
        }
        await finish(cred);
      })
    }, 'Sign in');

    const createBtn = el('button', {
      type: 'button', class: 'vp-auth__btn ' + (isSignup ? 'vp-auth__primary' : 'vp-auth__secondary'), onclick: guard(async () => {
        const cred = await createUserWithEmailAndPassword(auth, needEmail(), pass.value);
        await sendEmailVerification(cred.user);
        await signOut(auth);
        say('Account created. We sent a verification link to ' + email.value.trim() + ' — open it, then sign in.');
        throw null;
      })
    }, 'Create account');

    const emailKids = isSignup
      ? [email, pass, createBtn, signInBtn]
      : [email, pass, signInBtn, createBtn];
    emailKids.push(el('div', { class: 'vp-auth__row' },
      el('button', {
        type: 'button', class: 'vp-auth__link', onclick: guard(async () => {
          await sendPasswordResetEmail(auth, needEmail());
          say('Password reset link sent to ' + email.value.trim() + '.');
        })
      }, 'Forgot password?'),
      el('button', {
        type: 'button', class: 'vp-auth__link', onclick: guard(async () => {
          const addr = needEmail();
          const url = new URL(window.location.href); url.searchParams.set('redirect_to', redirect);
          await sendSignInLinkToEmail(auth, addr, { url: url.toString(), handleCodeInApp: true });
          localStorage.setItem('vpAuthEmail', addr);
          say('Sign-in link sent to ' + addr + '. Open it on this device.');
        })
      }, 'Email me a sign-in link')));

    const emailPane = el('div', { class: 'vp-auth__pane', 'data-pane': 'email', hidden: isSignup ? false : '' }, ...emailKids);

    const selectTab = (name) => {
      phonePane.hidden = name !== 'phone';
      emailPane.hidden = name !== 'email';
      tPhone.setAttribute('aria-selected', String(name === 'phone'));
      tEmail.setAttribute('aria-selected', String(name === 'email'));
      say('');
    };
    const tPhone = el('button', { type: 'button', role: 'tab', class: 'vp-auth__tab', 'aria-selected': isSignup ? 'false' : 'true', onclick: () => selectTab('phone') }, 'Mobile OTP');
    const tEmail = el('button', { type: 'button', role: 'tab', class: 'vp-auth__tab', 'aria-selected': isSignup ? 'true' : 'false', onclick: () => selectTab('email') }, icon('email'), el('span', {}, 'Email'));

    const brand = cfg.brand || 'Varanasi Pooja';
    const title = isSignup ? ('Create your ' + brand + ' account') : ('Sign in to ' + brand);
    const subText = isSignup
      ? 'Use Google, Facebook, email, or your mobile number.'
      : 'New here? An account is created for you automatically.';

    const otherHref = isSignup ? (cfg.signIn || '/') : (cfg.signUp || cfg.signIn || '/');
    const switcher = el('p', { class: 'vp-auth__switch' });
    const switchLink = el('a', { class: 'vp-auth__link', href: otherHref });
    switchLink.textContent = isSignup ? 'Sign in' : 'Create an account';
    switcher.append(isSignup ? 'Already have an account? ' : 'Need an account? ', switchLink);

    root.replaceChildren(
      el('h2', { class: 'vp-auth__title' }, title),
      el('p', { class: 'vp-auth__sub' }, subText),
      googleBtn,
      facebookBtn,
      el('div', { class: 'vp-auth__divider' }, el('span', {}, 'or')),
      el('div', { class: 'vp-auth__tabs', role: 'tablist' }, tPhone, tEmail),
      phonePane, emailPane, status, switcher,
    );

    if (isSignInWithEmailLink(auth, window.location.href)) {
      run(async () => {
        let addr = localStorage.getItem('vpAuthEmail') || '';
        if (!addr) addr = (window.prompt('Please confirm your email address') || '').trim();
        if (!addr) throw { code: 'auth/invalid-email' };
        try { await finish(await signInWithEmailLink(auth, addr, window.location.href)); } catch (e) { if (e !== null) throw e; }
        localStorage.removeItem('vpAuthEmail');
      });
    }
  }
}
