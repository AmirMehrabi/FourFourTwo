import { teamNames, ui } from '../translations/fa.js';

export function useTranslations() {
    const translateTeamName = (teamName) => {
        return teamNames[teamName] || teamName;
    };

    const translate = (key) => {
        return ui[key] || key;
    };

    return {
        translateTeamName,
        translate,
        t: translate // Short alias
    };
}
