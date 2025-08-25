import Cookies from 'universal-cookie';

export function getCookie(name: string) {
  const cookies = new Cookies(null, { path: '/' });
  return cookies.get(name);
}

export function setCookie(name: string, value: string) {
  const cookies = new Cookies(null, { path: '/' });
  cookies.set(name, value);
}
